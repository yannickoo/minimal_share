<?php

namespace Drupal\minimal_share\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Minimal Share provider plugin manager.
 */
class MinimalShareProviderManager extends DefaultPluginManager {

  /**
   * Constructor for MinimalShareProviderManager objects.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MinimalShareProvider', $namespaces, $module_handler, 'Drupal\minimal_share\Plugin\MinimalShareProviderInterface', 'Drupal\minimal_share\Annotation\MinimalShareProvider');

    $this->alterInfo('minimal_share_minimal_share_provider_info');
    $this->setCacheBackend($cache_backend, 'minimal_share_minimal_share_provider_plugins');
  }

}
