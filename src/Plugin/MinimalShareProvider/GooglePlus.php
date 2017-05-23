<?php

namespace Drupal\minimal_share\Plugin\MinimalShareProvider;

use Drupal\Component\Serialization\Json;
use Drupal\minimal_share\Plugin\MinimalShareProviderBase;
use GuzzleHttp\Client;

/**
 * @MinimalShareProvider(
 *   id = "google_plus",
 *   label = @Translation("Google+"),
 *   url = "https://plus.google.com/share?url=[url]",
 *   color = "#db4a39",
 *   size = {
 *     "width" = 600,
 *     "height" = 400,
 *   },
 *   count = TRUE,
 * )
 */
class GooglePlus extends MinimalShareProviderBase {

  public function getCount($url) {
    $count = 0;
    $count_url = 'https://clients6.google.com/rpc';

    /** @var Client $client */
    $client = \Drupal::httpClient();

    try {
      $request = $client->post($count_url, [
        'json' => Json::decode('[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]'),
      ]);
    } catch (\Exception $e) {
      return $count;
    }

    $data = Json::decode($request->getBody());

    if (!empty($data[0]['result']['metadata']['globalCounts']['count'])) {
      $count = $data[0]['result']['metadata']['globalCounts']['count'];
    }

    return $count;
  }

}
