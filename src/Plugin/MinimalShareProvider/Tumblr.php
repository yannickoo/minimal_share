<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "tumblr",
 *   label = @Translation("Tumblr"),
 *   url = "https://www.tumblr.com/share/link?url=[url]&name=[title]&description=[description]",
 *   color = "#32506d",
 *   size = {
 *     "width" = 455,
 *     "height" = 455,
 *   },
 * )
 */
class Tumblr extends MinimalShareProviderBase {

}
