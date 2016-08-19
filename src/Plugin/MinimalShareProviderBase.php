<?php

namespace Drupal\minimal_share\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Minimal Share provider plugins.
 */
abstract class MinimalShareProviderBase extends PluginBase implements MinimalShareProviderInterface {

  /**
   * Get share count for a specific URL.
   *
   * @param string $url
   *   The URL to count.
   * @return int
   *   The amount of shares.
   */
  public function getCount($url) {
    return 0;
  }

  /**
   * Get icon path.
   *
   * @return string
   */
  public function getIconPath() {
    return drupal_get_path('module', 'minimal_share') . '/assets/icons/';
  }
}
