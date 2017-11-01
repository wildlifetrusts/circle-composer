<?php

namespace Drupal\wildlife_field_formatters\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsFormatterBase;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'entity reference limit by field rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_limit_by_field_entity_view",
 *   label = @Translation("Rendered entity (limited by field)"),
 *   description = @Translation("Display the referenced entities limitted by a field rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceLimitByFieldEntityFormatter extends EntityReferenceRevisionsFormatterBase implements ContainerFactoryPluginInterface {

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
   * The field formatter plugin instance for entityReferenceRevisions.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $entityReferenceEntityFormatter;

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
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityDisplayRepositoryInterface $entity_display_repository, FormatterInterface $entity_referenc_entity_formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityReferenceEntityFormatter = $entity_referenc_entity_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter_manager = $container->get('plugin.manager.field.formatter');
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity_display.repository'),
      $formatter_manager->createInstance('entity_reference_entity_view', $configuration)
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'view_mode' => 'default',
        'field' => NULL,
      ] + parent::defaultSettings();
  }

  /**
   * @param $entity_type
   *   The entity type.
   * @param $bundle
   *   The bundle.
   * @param $field_name
   *   The field machine name.
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   */
  protected function getFieldName($entity_type, $bundle, $field_name) {
    /** @var \Drupal\field\Entity\FieldConfig $field_config */
    $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
    return $field_config->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = $this->entityReferenceEntityFormatter->settingsForm($form, $form_state);

    $field_options = [];

    foreach($form['#fields'] as $field) {
      $field_options[$field] = $this->getFieldName($form['#entity_type'], $form['#bundle'], $field);
    }

    $elements['field'] = [
      '#type' => 'select',
      '#options' => $field_options,
      '#title' => t('Field to use'),
      '#default_value' => $this->getSetting('field'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = $this->entityReferenceEntityFormatter->settingsSummary();

    $entity_type = $this->fieldDefinition->getTargetEntityTypeId();
    $bundle = $this->fieldDefinition->getTargetBundle();
    $field = $this->getFieldName($entity_type, $bundle, $this->getSetting('field'));
    $summary[] = t('Limited using field "@field"', ['@field' => isset($fields[$field]) ? $fields[$field] : $field]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $parent_entity = $items->getParent()->getValue();
    $limiting_field = $this->getSetting('field');

    if (!$parent_entity->get($limiting_field)->isEmpty()) {
      $limiting_value = $parent_entity->get($limiting_field)->getString();
      $limit = intval($limiting_value);

      if ($limit && $items->count() > $limit) {
        foreach ($items->getValue() as $key => $item) {
          if ($key + 1 > $limit) {
            $items->removeItem($key);
          }
        }
      }
    }

    $elements = $this->entityReferenceEntityFormatter->viewElements($items, $langcode);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available on featured_cards bundles.
    $bundle = $field_definition->getTargetBundle();

    return $bundle == 'featured_cards';
  }
}
