<?php

namespace Drupal\minimal_share\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Minimal Share provider item annotation object.
 *
 * @see \Drupal\minimal_share\Plugin\MinimalShareProviderManager
 * @see plugin_api
 *
 * @Annotation
 */
class MinimalShareProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The URL of the share dialog.
   *
   * @var string
   */
  public $url;

  /**
   * The primary color of the provider.
   *
   * @var string
   */
  public $color;

  /**
   * The size of the popup window.
   *
   * @var object
   */
  public $size;

  /**
   * Whether provider supports sharing count.
   *
   * @var bool
   */
  public $count;

  /**
   * Whether this provider is only available on mobile devices.
   *
   * @var bool
   */
  public $mobile;

  /**
   * The download URL to install the app if provider uses custom URL scheme.
   * It is recommended to use a service like http://appurl.io to provide
   * OS specific redirects.
   *
   * @var string
   */
  public $download;

}
