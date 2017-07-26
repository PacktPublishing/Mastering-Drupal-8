<?php

namespace Drupal\mastering_drupal_8;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Class WeatherUnderground.
 */
class WeatherUnderground {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;
  /**
   * Constructs a new WeatherUnderground object.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Returns information about current hurricanes and tropical storms
   * 
   * @return ResponseInterface
   */
  public function getCurrentHurricane() {
    $this->httpClient->get('http://api.wunderground.com/api/{key}/currenthurricane/view.json');
  }
}
