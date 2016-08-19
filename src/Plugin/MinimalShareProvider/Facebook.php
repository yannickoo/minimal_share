<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\Component\Serialization\Json;
use Drupal\minimal_share\Plugin\MinimalShareProviderBase;

/**
 * @MinimalShareProvider(
 *   id = "facebook",
 *   label = @Translation("Facebook"),
 *   url = "https://www.facebook.com/sharer.php?u=[url]",
 *   color = "#3b5998",
 *   size = {
 *     "width" = 600,
 *     "height" = 500,
 *   },
 *   count = TRUE,
 * )
 */
class Facebook extends MinimalShareProviderBase {

  /**
   * {@inheritdoc}
   */
  public function getCount($url) {
    $count = 0;
    $count_url = 'http://graph.facebook.com/?id=' . $url;

    /** @var Client $client */
    $client = \Drupal::httpClient();
    $request = $client->get($count_url);

    $data = Json::decode($request->getBody());
    if (!empty($data['share']['share_count'])) {
      $count = $data['share']['share_count'];
    }

    return $count;
  }

}
