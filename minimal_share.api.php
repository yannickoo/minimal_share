<?php

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Register a new service.
 *
 * This hook is invoked by minimal_share_services().
 *
 * @param $settings
 *   An associative array of all defined services.
 */
function hook_minimal_share_services() {
  $services['drupalorg'] = array(
    'title' => t('Drupal.org'),
    'link' => 'https://www.drupal.org/share.php?u=[url]&t=[title]',
    'size' => array('width' => '600', 'height' => '500'),
  );
}


/**
 * Modify available services.
 *
 * This hook is invoked by minimal_share_services().
 *
 * @param $settings
 *   An associative array of all defined services.
 */
function hook_minimal_share_services_alter(&$services) {
  $services['drupalorg']['size']['width'] = '500';
}
