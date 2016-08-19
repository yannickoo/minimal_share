<?php

namespace Drupal\minimal_share\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\minimal_share\MinimalShareManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\minimal_share\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\minimal_share\MinimalShareManager
   */
  protected $manager;

  /**
   * ConfigForm constructor.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   * @param \Drupal\minimal_share\MinimalShareManager $minimal_share_manager
   */
  public function __construct(DateFormatterInterface $date_formatter, MinimalShareManager $minimal_share_manager) {
    $this->dateFormatter = $date_formatter;
    $this->manager = $minimal_share_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('minimal_share.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'minimal_share.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minimal_share_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->manager->getConfig();
    $providers = $this->manager->getProviders(TRUE);
    $form['#tree'] = TRUE;

    $form['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enabled entity types'),
      '#options' => $this->manager->getContentEntityTypes(),
      '#default_value' => !empty($config['entity_types']) ? $config['entity_types'] : [],
      '#description' => $this->t('Enable Minimal Share for specific entity types.'),
    ];

    $form['providers'] = [
      '#type' => 'details',
      '#title' => $this->t('Providers'),
      '#open' => TRUE,
    ];

    foreach ($providers as $provider => $definition) {
      $form['providers'][$provider] = [
        '#type' => 'fieldset',
        '#title' => $definition['label'],
      ];

      $form['providers'][$provider]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable'),
        '#default_value' => !empty($providers[$provider]['enabled']),
      ];

      $form['providers'][$provider]['config'] = [
        '#type' => 'container',
      ];

      // We use a random number for demonstrating the count labels.
      $count = rand(1, 50);

      $label_options = array(
        'name' => $definition['label'],
        'icon' => '',
        'name_count' => $definition['label']->render() . ' (' . $count . ')',
        'custom' => $this->t('Custom'),
      );

      // Add 'count' radio only if there is a count callback set.
      if (empty($definition['count'])) {
        unset($label_options['name_count']);
      }

      $form['providers'][$provider]['label_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Label'),
        '#description' => $this->t('Set the label for @provider share links.', ['@provider' => $definition['label']]),
        '#options' => $label_options,
        '#default_value' => !empty($providers[$provider]['label_type']) ? $providers[$provider]['label_type'] : 'name',
        '#states' => [
          'visible' => [
            'input[name="providers[' . $provider . '][enabled]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['providers'][$provider]['hide_zero'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Hide zero'),
        '#description' => $this->t('Hide the share count if it is  zero.'),
        '#default_value' => !empty($providers[$provider]['hide_zero']) ? $providers[$provider]['hide_zero'] : 0,
        '#states' => [
          'visible' => [
            'input[name="providers[' . $provider . '][enabled]"]' => ['checked' => TRUE],
            'input[name="providers[' . $provider . '][label_type]"]' => ['value' => 'name_count'],
          ],
        ],
      ];

      $form['providers'][$provider]['custom'] = [
        '#type' => 'textfield',
        '#title' => t('Custom label'),
        '#description' => !empty($definition['count']) ? $this->t('Use <code>[count]</code> to display the sharing count.') : '',
        '#default_value' => isset($service['custom']) ? $service['custom'] : '',
        '#states' => [
          'visible' => [
            'input[name="providers[' . $provider . '][enabled]"]' => ['checked' => TRUE],
            'input[name="providers[' . $provider . '][label_type]"]' => ['value' => 'custom'],
          ],
          'required' => [
            'input[name="providers[' . $provider . '][enabled]"]' => ['checked' => TRUE],
            'input[name="providers[' . $provider . '][label_type]"]' => ['value' => 'custom'],
          ],
        ],
      ];

    }

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
    ];

    $intervals = [0, 60, 180, 300, 600, 900, 1800, 2700, 3600, 10800, 21600, 32400, 43200, 86400];
    $period = array_combine($intervals, array_map([$this->dateFormatter, 'formatInterval'], $intervals));

    $period[0] = '<' . t('none') . '>';

    $form['advanced']['cache_lifetime'] = [
      '#type' => 'select',
      '#title' => t('Cache lifetime'),
      '#options' => $period,
      '#description' => $this->t('Cached counts will not be re-fetched until at least this much time has elapsed. When no cache lifetime is selected the page load takes much more time because counts are fetched on each request then.'),
      '#default_value' => isset($settings['advanced']['cache_lifetime']) ? $settings['advanced']['cache_lifetime'] : 900,
    ];

    $form['advanced']['icon_type'] = [
      '#type' => 'select',
      '#title' => t('Icon embed type'),
      '#options' => [
        'inline' => $this->t('Inline SVG'),
        'image_tag' => $this->t('Image tag'),
        'background' => $this->t('Background image'),
      ],
      '#description' => $this->t('Select which method should be used for embedding the icon.'),
      '#default_value' => isset($settings['advanced']['icon_type']) ? $settings['advanced']['icon_type'] : 'inline',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();
    $providers = $values['providers'];
    $prepared_providers = [];
    $i = 0;

    foreach ($providers as $provider => $config) {
      if (empty($config['enabled'])) {
        continue;
      }

      // Clean-up the configuration.
      foreach (['custom', 'hide_zero'] as $property) {
        if (empty($config[$property])) {
          unset($config[$property]);
        }
      }

      $prepared_providers[$i] = [
        $provider => $config,
      ];

      $i++;
    }

    $enabled_entity_types = [];
    foreach ($values['entity_types'] as $entity_type => $enabled) {
      if (!empty($enabled)) {
        $enabled_entity_types[] = $entity_type;
      }
    }

    $this->config('minimal_share.config')
      ->set('entity_types', $enabled_entity_types)
      ->set('providers', $prepared_providers)
      ->set('advanced', $values['advanced'])
      ->save();
  }

}
