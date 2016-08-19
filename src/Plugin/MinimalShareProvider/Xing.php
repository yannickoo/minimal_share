<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "xing",
 *   label = @Translation("Xing"),
 *   url = "https://www.xing.com/spi/shares/new?url=[url]",
 *   color = "#005A5f",
 *   size = {
 *     "width" = 520,
 *     "height" = 570,
 *   },
 * )
 */
class Xing extends MinimalShareProviderBase {}
