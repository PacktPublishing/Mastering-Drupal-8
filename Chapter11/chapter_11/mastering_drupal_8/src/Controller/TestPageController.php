<?php
namespace Drupal\mastering_drupal_8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Console\Output\Output;
use Drupal\node\NodeInterface;
use Drupal\block\BlockInterface;

/**
 * Class TestPageController
 */
class TestPageController extends ControllerBase {
  /**
   * Test.
   *
   * @return string
   *   Return Test string.
   */
  public function test() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Test")
    ];
  }
  
  /**
   * Test message.
   *
   * @param string $message
   *   The message to output
   *   
   * @return string
   *   Return Test string.
   */
  public function testMessage($message) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Test: " . $message)
    ];
  }
  
  /**
   * Test node.
   *
   * @param NodeInterface $node
   *  The Node passed to our function
   *  
   * @return string
   *   Return Test string.
   */
  public function testNode(NodeInterface $node) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Test: " . $node->getTitle()),
    ];
  }
  
  /**
   * Test Block.
   *
   * @param BlockInterface $block
   *   The block passed to our function
   *   
   * @return string
   *   Return Test string.
   */
  public function testBlock(BlockInterface $block) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t("Test"),
    ];
  }
}
