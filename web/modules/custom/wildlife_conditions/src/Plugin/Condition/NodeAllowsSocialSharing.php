<?php

namespace Drupal\wildlife_conditions\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Node allows social sharing' condition.
 *
 * @Condition(
 *   id = "node_allows_social_sharing",
 *   label = @Translation("Show on nodes which allow social sharing"),
 * )
 */
class NodeAllowsSocialSharing extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a new NodeType instance.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match interface.
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
  public function __construct(RouteMatchInterface $route_match, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
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
    $form['display_on_social_sharing_nodes'] = [
      '#title' => $this->t('Show on nodes which have Social Sharing allowed.'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['display_on_social_sharing_nodes'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['display_on_social_sharing_nodes'] = $form_state->getValue('display_on_social_sharing_nodes');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $condition = $this->configuration['display_on_social_sharing_nodes'] ? 'with' : 'except those with';

    return $this->t('Show on all nodes @condition Social Sharing allowed.', ['@condition' => $condition]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if ($this->configuration['display_on_social_sharing_nodes']) {
      /** @var \Drupal\node\Entity\Node $node */
      if ($node = $this->routeMatch->getParameter('node')) {
        $social_sharing = $node->get('social_sharing')->getValue();
        if (!empty($social_sharing) && $social_sharing[0]['value'] == '1') {
          return TRUE;
        }
      }

      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['display_on_social_sharing_nodes' => null] + parent::defaultConfiguration();
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
