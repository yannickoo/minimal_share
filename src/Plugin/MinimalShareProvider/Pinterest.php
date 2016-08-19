<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "pinterest",
 *   label = @Translation("Pinterest"),
 *   url = "http://pinterest.com/pin/create/button/?url=[url]&description=[title]&media=[media]",
 *   color = "#cb2027",
 *   size = {
 *     "width" = 1000,
 *     "height" = 600,
 *   },
 * )
 */
class Pinterest extends MinimalShareProviderBase {

}
