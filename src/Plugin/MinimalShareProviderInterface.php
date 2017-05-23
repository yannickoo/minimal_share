<?php

namespace Drupal\minimal_share\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Minimal Share provider plugins.
 */
interface MinimalShareProviderInterface extends PluginInspectionInterface {

  /**
   * Get share count for a specific URL.
   *
   * @param string $url
   *   The URL to count.
   * @return int
   *   The amount of shares.
   */
  public function getCount($url);

  /**
   * Get icon path.
   *
   * @return string
   */
  public function getIconPath();

}
