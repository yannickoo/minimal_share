<?php

namespace Drupal\minimal_share\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Minimal Share provider plugins.
 */
abstract class MinimalShareProviderBase extends PluginBase implements MinimalShareProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getCount($url) {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconPath() {
    return drupal_get_path('module', 'minimal_share') . '/assets/icons/';
  }
}
