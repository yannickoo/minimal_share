<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "twitter",
 *   label = @Translation("Twitter"),
 *   url = "https://twitter.com/intent/tweet?status=[title]%20-%20[url]",
 *   color = "#0099d2",
 *   size = {
 *     "width" = 600,
 *     "height" = 260,
 *   },
 * )
 */
class Twitter extends MinimalShareProviderBase {}
