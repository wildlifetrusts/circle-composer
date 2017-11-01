<?php

namespace Drupal\wildlife_content\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\Migration;
use Drupal\migrate\Plugin\MigratePluginManagerInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process an example content based on format supplied.
 *
 * @MigrateProcessPlugin(
 *   id = "wildlife_content_menu_link"
 * )
 */
class WildlifeContentMenuLink extends Migration implements ContainerFactoryPluginInterface  {

  /**
   * The entity type manager, used to fetch entity link templates.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, MigrationInterface $migration, MigrationPluginManagerInterface $migration_plugin_manager, MigratePluginManagerInterface $process_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $migration_plugin_manager, $process_plugin_manager);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $migration,
      $container->get('plugin.manager.migration'),
      $container->get('plugin.manager.migrate.process')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Tidy up the path.
    list($path) = $value;
    $path = ltrim($path, '/');

    if (parse_url($path, PHP_URL_HOST)) {
      return $path;
    }

    // Attempt to parse out the path.
    if ($path = parse_url($path, PHP_URL_PATH)) {
      // Explode the path into parts so we can inspect each.
      $path_parts = explode('/', $path);
      // Check if this looks like a node URL.
      if (isset($path_parts[0]) && $path_parts[0] == 'node' && isset($path_parts[1])) {
        // If it does, determine the destination ID from the source ID within the URL.
        if ($path_parts[1] = parent::transform($path_parts[1], $migrate_executable, $row, $destination_property)) {
          return 'entity:node/' . $path_parts[1];
        }
      }
    }

    if (parse_url($path, PHP_URL_SCHEME) === NULL) {
      if (strpos($path, '<') !== FALSE && strpos($path, '>') !== FALSE) {
        return 'route:'. $path;
      } else {
        return 'internal:/' . ltrim($path, '/');
      }
    }

    return $path;
  }

}

