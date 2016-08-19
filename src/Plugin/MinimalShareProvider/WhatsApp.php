<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "whatsapp",
 *   label = @Translation("WhatsApp"),
 *   url = "whatsapp://send?text=[title]%20-%20[url]",
 *   color = "#4dc247",
 *   download = "http://m.appurl.io/is05lb9m"
 * )
 */
class WhatsApp extends MinimalShareProviderBase {}
