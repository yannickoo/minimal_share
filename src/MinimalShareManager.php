<?php

namespace Drupal\minimal_share;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\minimal_share\Plugin\MinimalShareProviderInterface;
use Drupal\minimal_share\Plugin\MinimalShareProviderManager;

/**
 * Class MinimalShareManager.
 *
 * @package Drupal\minimal_share
 */
class MinimalShareManager {

  /**
   * @var ImmutableConfig
   */
  protected $config;

  /**
   * @var MinimalShareProviderManager
   */
  protected $providerManager;

  /**
   * MinimalShareManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   * @param \Drupal\minimal_share\Plugin\MinimalShareProviderManager $provider_manager
   */
  public function __construct(ConfigFactory $config_factory, MinimalShareProviderManager $provider_manager) {
    $this->config = $config_factory->get('minimal_share.config')->get();
    $this->providerManager = $provider_manager;
  }

  /**
   * Get configuration.
   *
   * @return array
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Whether Minimal Share is enabled for a specific entity type.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return bool
   */
  public function entityTypeEnabled($entity_type_id) {
    return in_array($entity_type_id, $this->config['entity_types']);
  }

  /**
   * Sort providers by their weight and their enabled status.
   *
   * @param array $providers
   *   An array containing the providers to sort.
   */
  public function sortProviders(&$providers) {
    uasort($providers, [get_class($this), 'sortByWeight']);
    uasort($providers, [get_class($this), 'sortByEnabled']);
  }

  /**
   * Make sure a specific property exists on an array.
   *
   * @param array $array
   *   The array to verify.
   * @param string $property
   *   The property that should exists on array.
   *
   * @return array
   *   The adjusted array.
   */
  public static function addPropertyIfNotExists($array, $property) {
    if (!isset($array[$property])) {
      $array[$property] = '';
    }

    return $array;
  }

  /**
   * Sort an array by key.
   *
   * @param string $key
   *   The key you want to sort by.
   * @param string $a
   *   First string for comparison.
   * @param string $b
   *   Second string for comparison.
   * @param string $order
   *   Whether you want to sort asc or desc.
   *
   * @return int
   *   The return of strcmp().
   */
  public static function sortByKey($key, $a, $b, $order = 'asc') {
    $a = self::addPropertyIfNotExists($a, $key);
    $b = self::addPropertyIfNotExists($b, $key);

    if ($order == 'asc') {
      return strcmp($a[$key], $b[$key]);
    }
    else {
      return strcmp($b[$key], $a[$key]);
    }
  }

  /**
   * Sort an array by weight function.
   */
  public static function sortByWeight($a, $b) {
    return self::sortByKey('weight', $a, $b);
  }

  /**
   * Sort an array by enabled function.
   */
  public static function sortByEnabled($a, $b) {
    return self::sortByKey('enabled', $a, $b, 'desc');
  }

  /**
   * Get all defined providers.
   *
   * @param bool $with_config
   *   Whether config should be attached or not.
   *
   * @return array
   *   An array containing the providers.
   */
  public function getProviders($with_config = FALSE) {
    $provider_manager = \Drupal::service('plugin.manager.minimal_share_provider.processor');
    $providers = $provider_manager->getDefinitions();

    if (!$with_config) {
      return $providers;
    }

    $enabled_providers = $this->getEnabledProviders();
    $providers = array_merge($providers, $enabled_providers);

    $this->sortProviders($providers);

    return $providers;
  }

  /**
   * Get enabled providers.
   *
   * @return array
   */
  public function getEnabledProviders() {
    $all_providers = $this->getProviders();
    $providers = $this->config['providers'];
    $flatten = [];

    foreach ($providers as $weight => $provider) {
      $id = array_keys($provider)[0];

      $flatten[$id] = $provider[$id];
      $flatten[$id]['weight'] = $weight;

      $flatten[$id] = array_merge($flatten[$id], $all_providers[$id]);
    }

    $this->sortProviders($flatten);

    return $flatten;
  }

  /**
   * Convert string to machine-readable name.
   *
   * @param string $str
   *   The string to convert.
   *
   * @return string
   *   The machine-readable string.
   */
  public function getMachineName($str) {
    return Unicode::strtolower(Html::cleanCssIdentifier($str));
  }

  /**
   * Get all defined content entity types.
   *
   * @return array
   */
  public function getContentEntityTypes() {
    $list = [];
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();

    foreach ($entity_types as $entity_type_id => $entity_type) {
      if ($entity_type instanceof ContentEntityType) {
        $list[$entity_type_id] = $entity_type->getLabel();
      }
    }

    return $list;
  }

  /**
   * Build share info from entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function buildShareInfo(EntityInterface $entity) {
    return [
      'url' => $entity->toUrl('canonical', ['absolute' => TRUE])->toString(),
      'title' => $entity->label(),
    ];
  }

  /**
   * Build a Minimal Share widget render array.
   *
   * @param array $info
   *   The share info.
   *
   * @return array
   */
  public function build($info) {
    $providers = $this->getEnabledProviders();
    $build = [
      '#theme' => 'minimal_share',
      '#attached' => [
        'library' => ['minimal_share/minimal-share'],
      ],
      '#cache' => [
        'contexts' => ['url.path'],
        'tags' => ['config:minimal_share.config'],
      ],
    ];

    foreach ($providers as $provider => $config) {
      $build['#providers'][$provider] = $this->buildProviderItem($config, $info);
    }

    return $build;
  }

  /**
   * Build render array for a single provider item.
   *
   * @param array $provider
   *   The provider array.
   * @param array $info
   *   The share info.
   *
   * @return array
   */
  public function buildProviderItem($provider, $info) {
    $label = $this->getProviderLabel($provider, $info['url']);
    $url = $this->buildShareUrl($provider, $info);
    $attributes = $this->getAttributes($provider);
    $attributes->setAttribute('href', $url);

    $build = [
      '#theme' => 'minimal_share_item',
      '#title' => $label,
      '#attributes' => $attributes,
    ];

    if (!empty($provider['size'])) {
      $minimal_share_settings = &$build['#attached']['drupalSettings']['minimalShare'];

      $minimal_share_settings['sizes'] = [
        $provider['id'] => [
          'width' => $provider['size']['width'],
          'height' => $provider['size']['height'],
        ],
      ];
    }

    return $build;
  }

  /**
   * @param array $provider
   *   The provider array.
   * @param string $url
   *   The URL to share.
   *
   * @return array
   */
  public function getProviderLabel($provider, $url) {
    $label = [];

    if (empty($provider['label_type'])) {
      $provider['label_type'] = 'name';
    }

    if ($provider['label_type'] == 'name') {
      $label = $provider['label'];
    }
    elseif ($provider['label_type'] == 'icon') {
      $icon_type = $this->config['advanced']['icon_type'];
      $label = $this->buildIcon($provider['id'], $icon_type);
    }
    elseif ($provider['label_type'] == 'name_count') {
      $provider_label = $provider['label'];
      $count = $this->getShareCount($provider['id'], $url);
      $hide_zero = !empty($provider['hide_zero']) && empty($count);

      $label = [
        'label' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => new Attribute(['class' => ['ms-label']]),
          '#value' => $provider_label,
        ],
        'count' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => new Attribute(['class' => ['ms-count']]),
          '#value' => !$hide_zero ? '(' . $count . ')' : '',
        ],
      ];
    }
    elseif ($provider['label_type'] == 'custom') {
      $custom = $provider['custom'];
      $with_count = strpos($custom, '[count]') !== 0;

      // Check whether [count] placeholder exists in custom label and
      // replace it with the actual count.
      if ($with_count) {
        $count = $this->getShareCount($provider['id'], $url);
        if (!empty($provider['hide_zero']) && empty($count)) {
          $count = '';
        }

        $custom = str_replace('[count]', $count, $custom);
      }

      $label = [
        '#markup' => Html::escape($custom),
      ];
    }

    return $label;
  }

  /**
   * Build share URL.
   *
   * @param array $provider
   *   The specific provider.
   * @param $info
   *   The data to be shared.
   *
   * @return string
   */
  public function buildShareUrl($provider, $info) {
    if (empty($provider['url'])) {
      return '';
    }

    $share_url = $provider['url'];
    $title = $info['title'];
    $url = $info['url'];

    $url = str_replace(['[url]', '[title]'], [$url, $title], $share_url);

    return $url;
  }

  public function buildIcon($provider_id, $icon_type) {
    $label = [];
    /** @var MinimalShareProviderInterface $provider */
    $provider = $this->providerManager->createInstance($provider_id);
    $icon_name = Html::cleanCssIdentifier($provider_id);
    $icon_path = $provider->getIconPath() . $icon_name . '.svg';
    $icon_url = Url::fromUri('base://' . $icon_path)->toString();

    if ($icon_type == 'inline') {
      $svg_content = file_get_contents($icon_path);

      $label = [
        '#markup' => $svg_content,
        '#allowed_tags' => ['svg', 'g', 'path', 'text'],
      ];
    }
    elseif ($icon_type == 'image_tag') {
      $label = [
        '#markup' => '<img src="' . $icon_url . '">',
      ];
    }
    elseif ($icon_type == 'background') {
      $clean_provider = Html::cleanCssIdentifier($provider_id);
      $style = [
        [
          '#tag' => 'style',
          '#value' => '.minimal-share .' . $clean_provider . ' {background-image: url(' . $icon_url . ');}',
        ],
        'minimal_share_provider_' . $provider_id,
      ];

      $label = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attached' => [
          'html_head' => [$style],
        ],
      ];
    }

    return $label;
  }

  /**
   * Get link attributes for a specific provider.
   *
   * @param array $provider
   *   The specific provider.
   *
   * @return Attribute
   */
  public function getAttributes($provider) {
    $attributes = new Attribute();
    $attributes->setAttribute('href', '');
    $attributes->setAttribute('title', t('Share this via @provider', [
      '@provider' => $provider['label'],
    ]));
    $attributes->addClass(Html::cleanCssIdentifier($provider['id']));
    $attributes->setAttribute('data-ms', $provider['id']);

    if (!empty($provider['mobile'])) {
      $attributes->addClass('ms-mobile-only');
    }

    if ($provider['label_type'] == 'icon') {
      $attributes->addClass('ms-icon');
    }

    return $attributes;
  }

  /**
   * Get share count for a specific URL + provider.
   *
   * @param string $provider_id
   *   The provider ID.
   * @param string $url
   *   The URL to count.
   *
   * @return int
   */
  public function getShareCount($provider_id, $url) {
    /** @var MinimalShareProviderInterface $provider */
    $provider = $this->providerManager->createInstance($provider_id);
    $cid = 'minimal_share:' . md5($provider_id . $url);

    if ($cache = \Drupal::cache()->get($cid)) {
      return (int) $cache->data;
    }
    else {
      $max_age = $this->config['advanced']['cache_lifetime'];
      $count = $provider->getCount($url);

      \Drupal::cache()->set($cid, $count, $max_age);

      return $count;
    }
  }

}
