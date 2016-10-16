<?php

namespace Drupal\admin_actions\Form;

use Drupal\system\Entity\Action;
use Drupal\Core\Entity\ContentEntityInterface;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the button form for the actions block.
 */
class AdminActionsBlockForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    );
  }

  /**
   * Constructs a new AdminActionsBlockForm.
   *
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_actions_block_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to operate on.
   * @param \Drupal\system\Entity\Action[] $actions
   *   List of allowed actions.
   *
   * @return array
   *   Renderable.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity = NULL, $actions = []) {

    $form['entity_type'] = [
      '#type' => 'hidden',
      '#value' => $entity->getEntityTypeId(),
    ];
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $entity->id(),
    ];

    /** @var \Drupal\system\Entity\Action $action */
    foreach ($actions as $action_id => $action) {
      $form[$action_id] = [
        '#type' => 'submit',
        '#value' => $action->get('label'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $entity_id = $values['entity_id'];
    $entity_type = $values['entity_type'];
    $op = $values['op'];
    drupal_set_message("Running $op on $entity_type:$entity_id");
  }

}
