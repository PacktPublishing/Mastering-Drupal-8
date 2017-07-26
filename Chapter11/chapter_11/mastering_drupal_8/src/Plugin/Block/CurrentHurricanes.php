<?php

namespace Drupal\mastering_drupal_8\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mastering_drupal_8\WeatherUnderground;

/**
 * Provides a 'CurrentHurricanes' block.
 *
 * @Block(
 *  id = "current_hurricanes",
 *  admin_label = @Translation("Current hurricanes"),
 * )
 */
class CurrentHurricanes extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\mastering_drupal_8\WeatherUnderground definition.
   *
   * @var \Drupal\mastering_drupal_8\WeatherUnderground
   */
  protected $masteringDrupal8WeatherUnderground;
  /**
   * Constructs a new CurrentHurricanes object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        WeatherUnderground $mastering_drupal_8_weather_underground
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->masteringDrupal8WeatherUnderground = $mastering_drupal_8_weather_underground;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('mastering_drupal_8.weather_underground')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_hurricanes = json_decode($this->masteringDrupal8WeatherUnderground->getCurrentHurricane()->getBody());
    
    $build = [];
    $build['list'] = [
      '#theme' => 'item_list',
      '#items' => [],
    ];
    
    foreach ($current_hurricanes->currenthurricane as $hurricane) {
      $build['list']['#items'][] = $hurricane->stormName_Nice;
    }
    
    if (0 === sizeof($current_hurricanes->currenthurricane)) {
      $build['list']['#items'][] = $this->t('None');
    }

    return $build;
  }
}
