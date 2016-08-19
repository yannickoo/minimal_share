<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "email",
 *   label = @Translation("Email"),
 *   url = "mailto:?subject=[title]&body=[url]",
 *   color = "#666",
 * )
 */
class Email extends MinimalShareProviderBase {

}
