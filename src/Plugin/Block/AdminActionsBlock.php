<?php

namespace Drupal\article\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Admin Actions' block.
 *
 * @Block(
 *   id = "admin_actions_block",
 *   admin_label = @Translation("Admin Actions block"),
 *   category = @Translation("Provides buttons to trigger actions that can be performed on an entity")
 * )
 */
class AdminActionsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#markup' => 'Action buttons go here.',
    );
  }

}
