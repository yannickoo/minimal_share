<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\Component\Serialization\Json;
use Drupal\minimal_share\Plugin\MinimalShareProviderBase;
use GuzzleHttp\Client;

/**
 * @MinimalShareProvider(
 *   id = "linkedin",
 *   label = @Translation("LinkedIn"),
 *   url = "http://www.linkedin.com/shareArticle?mini=true&url=[url]&title=[title]",
 *   color = "#007bb6",
 *   size = {
 *     "width" = 520,
 *     "height" = 570,
 *   },
 *   count = TRUE,
 * )
 */
class LinkedIn extends MinimalShareProviderBase {

  /**
   * {@inheritdoc}
   */
  public function getCount($url) {
    $count = 0;
    $count_url = 'http://www.linkedin.com/countserv/count/share?url=' . $url . '&format=json';

    /** @var Client $client */
    $client = \Drupal::httpClient();

    try {
      $request = $client->get($count_url);
    } catch (\Exception $e) {
      return $count;
    }

    $data = Json::decode($request->getBody());
    if (!empty($data['count'])) {
      $count = $data['count'];
    }

    return $count;
  }

}
