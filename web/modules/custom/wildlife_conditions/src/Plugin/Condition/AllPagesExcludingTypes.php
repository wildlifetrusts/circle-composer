<?php

namespace Drupal\wildlife_conditions\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'All pages excluding content types' condition.
 *
 * @Condition(
 *   id = "all_pages_excluding_types",
 *   label = @Translation("All pages excluding specific content types"),
 * )
 */
class AllPagesExcludingTypes extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a new NodeType instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(EntityStorageInterface $entity_storage, RouteMatchInterface $route_match, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity.manager')->getStorage('node_type'),
      $container->get('current_route_match'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $node_types = $this->entityStorage->loadMultiple();
    foreach ($node_types as $type) {
      $options[$type->id()] = $type->label();
    }
    $form['bundles'] = [
      '#title' => $this->t('All pages except views and nodes of type...'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => $this->configuration['bundles'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['bundles'] = array_filter($form_state->getValue('bundles'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (count($this->configuration['bundles']) > 1) {
      $bundles = $this->configuration['bundles'];
      $last = array_pop($bundles);
      $bundles = implode(', ', $bundles);
      return $this->t('The node bundle is @bundles or @last', ['@bundles' => $bundles, '@last' => $last]);
    }
    $bundle = reset($this->configuration['bundles']);
    return $this->t('The node bundle is @bundle', ['@bundle' => $bundle]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    /** @var \Drupal\node\Entity\Node $node */
    if ($node = $this->routeMatch->getParameter('node')) {
      // If the page is a node, check to see if it has been excluded.
      $node_bundle = $node->getType();
      if (!in_array($node_bundle, $this->configuration['bundles'])) {
        return TRUE;
      }
    }
    elseif (\Drupal::routeMatch()->getParameter('view_id')) {
      return FALSE;
    }
    else {
      // Always return TRUE when the page is not a node.
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['bundles' => []] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'url.path';
    return $contexts;
  }
}
