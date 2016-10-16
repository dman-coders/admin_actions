<?php

namespace Drupal\admin_actions\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides a 'Admin Actions' block.
 *
 * Provides buttons to trigger actions that can be performed on an entity.
 *
 * Extending BlockBase means that I implements  BlockPluginInterface,
 *
 * Because I also implement ContainerFactoryPluginInterface,
 * I can have more control over what settings get passed to me
 * during __construct()
 *
 * I'm not fully sure what the context chunk here does, but it seems to make
 * it possible to get the current entity from the request.
 *
 * @Block(
 *   id = "admin_actions_block",
 *   admin_label = @Translation("Admin Actions block"),
 *   category = @Translation("Actions to apply to entities"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       required = FALSE,
 *       label = @Translation("Current Node")
 *     )
 *   }
 * )
 */
class AdminActionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * Passed in so we can get a list of available entities in order to list
   * their appropriate actions.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The route match.
   *
   * Used to retrieve the current contextual entity.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The action storage.
   *
   * These are the actions extracted from the entitymanager.
   * We list them in the config form.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $actionStorage;

  /**
   * An array of actions that can be executed.
   *
   * @var \Drupal\system\ActionConfigEntityInterface[]
   */
  protected $actions = array();

  /**
   * {@inheritdoc}
   *
   * Declaring the plugin create() lets us define the parameters/services
   * that are passed to the plugin __construct(). Dependency Injection bollox.
   *
   * The important bit is that I declare that I want
   * an entity manager - to provide the config list of rntity actions,
   * the current route match - to tell me the context of the entity being
   * viewed.
   *
   * @see __construct()
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * Constructs a new AdminActionsBlock.
   *
   * Parameters I expect to be given are enumerated in the create()
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The menu tree service.
   *
   * @see create()
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, RouteMatchInterface $route_match) {
    // As we are overriding BlockBase::__construct(),
    // need to give it what it expects.
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
    $this->actionStorage = $entity_manager->getStorage('action');
    $this->routeMatch = $route_match;
  }

  /**
   * Configurations for the block.
   *
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    // Internals slightly borrowed from
    // Drupal\system\Plugin\views\field\BulkForm.
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['include_exclude'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Available actions'),
      '#options' => array(
        'exclude' => $this->t('All actions, except selected'),
        'include' => $this->t('Only selected actions'),
      ),
      '#default_value' => $config['include_exclude'],
    );
    $form['selected_actions'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Selected actions'),
      '#options' => $this->getBulkOptions(FALSE),
      '#default_value' => $config['selected_actions'],
    );

    return $form;
  }

  /**
   * Save block configs.
   *
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('include_exclude', $form_state->getValue('include_exclude'));
    $this->setConfigurationValue('selected_actions', $form_state->getValue('selected_actions'));
  }

  /**
   * Produce the renderable block.
   *
   * The block here prepares the list of actions and tells the form what to do.
   *
   * {@inheritdoc}
   */
  public function build() {

    /** @var \Drupal\Core\Entity\ContentEntityInterface $node */
    /** @var \Drupal\node\Entity\Node $node */
    $entity = $this->routeMatch->getParameter('node');
    if (!$entity) {
      return [];
    }

    $config = $this->getConfiguration();
    // Generate the short list of actions.
    // Need an array_intersect_key() here. Do it by hand instead.
    $available_actions = $this->getActions($entity->getEntityTypeId());
    $actions = [];
    foreach (array_filter($config['selected_actions']) as $action_id) {
      $actions[$action_id] = $available_actions[$action_id];
    }

    // Pass in our two extra parameters to the form builder.
    return \Drupal::formBuilder()->getForm('Drupal\admin_actions\Form\AdminActionsBlockForm', $entity, $actions);
  }

  /**
   * {@inheritdoc}
   *
   * Setting this tells the system that the block is cached differently
   * for every route.
   * As seen in BookNavigationBlock.
   *
   * We probably want role access to affect caching also.
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route', 'user.permissions']);
  }

  /**
   * Returns the available operations for this form.
   *
   * @param bool $filtered
   *   (optional) Whether to filter actions to selected actions.
   *
   * @return array
   *   An associative array of operations, suitable for a select element.
   *
   * @see \Drupal\system\Plugin\views\field\BulkForm::getBulkOptions()
   */
  protected function getBulkOptions($filtered = TRUE) {
    $config = $this->getConfiguration();
    $options = array();
    $actions = $this->getActions('node');
    // Filter the action list.
    foreach ($actions as $id => $action) {
      if ($filtered) {
        $in_selected = in_array($id, $config['selected_actions']);
        // If the field is configured to include only the selected actions,
        // skip actions that were not selected.
        if (($config['include_exclude'] == 'include') && !$in_selected) {
          continue;
        }
        // Otherwise, if the field is configured to exclude the selected
        // actions, skip actions that were selected.
        elseif (($config['include_exclude'] == 'exclude') && $in_selected) {
          continue;
        }
      }
      $options[$id] = $action->label();
    }
    return $options;
  }

  /**
   * Return a list of actions valid for the given entity type.
   *
   * @param string $entity_type
   *   Entity type to get actions list for.
   *
   * @return \Drupal\system\ActionConfigEntityInterface[]
   *   List of actions.
   *
   * @see \Drupal\system\Plugin\views\field\BulkForm::init()
   */
  protected function getActions($entity_type) {
    // Filter the actions to only include those for this entity type.
    $this->actions = array_filter($this->actionStorage->loadMultiple(), function ($action) use ($entity_type) {
      /** @var \Drupal\system\ActionConfigEntityInterface $action */
      return $action->getType() == $entity_type;
    });
    return $this->actions;
  }

  /**
   * {@inheritdoc}
   *
   * Declaring this prevents undefined index warnings the first time.
   */
  public function defaultConfiguration() {
    return [
      'include_exclude' => FALSE,
      'selected_actions' => [],
    ];
  }

}
