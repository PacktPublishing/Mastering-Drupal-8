<?php

namespace Drupal\mastering_drupal_8\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'TestBlock' block.
 *
 * @Block(
 *  id = "test_block",
 *  admin_label = @Translation("Test block"),
 * )
 */
class TestBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['test_block']['#markup'] = 'Implement TestBlock.';

    return $build;
  }

}
