<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference rendered first of type entity' formatter.
 *
 * @FieldFormatter(
 *   id = "first_of_type_view",
 *   label = @Translation("Rendered entity first of type"),
 *   description = @Translation("Display the first referenced entity of a given type rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions",
 *     "entity_reference",
 *   }
 * )
 */
class FirstOfTypeFormatter extends EntityReferenceRevisionsFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'view_mode' => 'default',
        'link' => FALSE,
        'type' => 'any',
      ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['view_mode'] = array(
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type')),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    );

    $elements['type'] = array(
      '#type' => 'select',
      '#options' => ['any' => 'Any (first, regardless of type)'] + $this->getTargetBundles(TRUE),
      '#title' => $this->t('Choose type'),
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $view_modes = $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $view_mode = $this->getSetting('view_mode');

    $type_setting = $this->getSetting('type');
    $type = $type_setting == 'any' ? 'of Any' : $this->getTargetBundleLabel($type_setting);

    $replacements = array(
      '@mode' => isset($view_modes[$view_mode]) ? $view_modes[$view_mode] : $view_mode,
      '@type' => $type == 'any' ? 'entity' : $type . ' entity',
    );
    $summary[] = $this->t('The first @type type, rendered as @mode', $replacements);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_mode = $this->getSetting('view_mode');
    $type = $this->getSetting('type');
    $elements = array();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ($type == 'any' || $entity->bundle() == $type) {
        // Protect ourselves from recursive rendering.
        static $depth = 0;
        $depth++;
        if ($depth > 20) {
          $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', array('@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()));
          return $elements;
        }
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
        $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

        // Add a resource attribute to set the mapping property's value to the
        // entity's url. Since we don't know what the markup of the entity will
        // be, we shouldn't rely on it for structured data such as RDFa.
        if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
          $items[$delta]->_attributes += array('resource' => $entity->toUrl()->toString());
        }
        $depth = 0;

        return $elements;
      }

    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that have a view
    // builder.
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    return \Drupal::entityTypeManager()->getDefinition($target_type)->hasViewBuilderClass();
  }

  /**
   * Get an array of target bundles.
   *
   * @param bool $human
   *   Whether or not the array should have human readable values.
   *
   * @return mixed
   *   An array of target bundles for the field.
   */
  private function getTargetBundles($human = FALSE) {
    $target_bundles = $this->getFieldSetting('handler_settings')['target_bundles'];

    if ($human) {
      foreach ($target_bundles as $key => $bundle) {
        $target_bundles[$key] = $this->getTargetBundleLabel($key);
      }
    }

    $target_bundles = (!empty($target_bundles) && count($target_bundles) > 1) ? $target_bundles : [];

    return $target_bundles;
  }

  /**
   * Get the human readable bundle label for a paragraph type.
   *
   * @param $bundle
   *   The machine name of the paragraph bundle.
   *
   * @return null|string
   *   The label of the paragraph bundle.
   */
  private function getTargetBundleLabel($bundle) {
    $entity_manager = \Drupal::entityTypeManager();
    $field_storage_config_settings = $this->fieldDefinition->getFieldStorageDefinition()->getSettings();
    $entity_definition = $entity_manager->getDefinition($field_storage_config_settings['target_type']);

    $bundle_label = \Drupal::entityTypeManager()
      ->getStorage($entity_definition->getBundleEntityType())
      ->load($bundle)
      ->label();

    return $bundle_label;
  }

}
