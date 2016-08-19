<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "facebook_messenger",
 *   label = @Translation("Facebook Messenger"),
 *   url = "fb-messenger://share?link=[url]",
 *   color = "#0084ff",
 *   mobile = TRUE,
 *   download = "http://m.appurl.io/is05rlg5"
 * )
 */
class FacebookMessenger extends MinimalShareProviderBase {

}
