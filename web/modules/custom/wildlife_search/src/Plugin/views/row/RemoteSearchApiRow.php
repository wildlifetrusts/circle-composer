<?php

namespace Drupal\wildlife_search\Plugin\views\row;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Driver\Exception\Exception;
use Drupal\node\Entity\Node;
use Drupal\search_api\Plugin\views\row\SearchApiRow;
use Drupal\search_api\SearchApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a row plugin for displaying a result as a rendered item.
 *
 * @ViewsRow(
 *   id = "remote_search_api",
 *   title = @Translation("Rendered entity (remote)"),
 *   help = @Translation("Displays remote entity of the matching search API item"),
 * )
 *
 * @see search_api_views_plugins_row_alter()
 */
class RemoteSearchApiRow extends SearchApiRow {
  /**
   * HTTP Client.
   *
   * @var \GuzzleHttp\Client $httpClient
   */
  protected $httpClient;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $row = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $row->httpClient = $container->get('http_client');

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    $datasource_id = $row->search_api_datasource;

    $uuid = $row->_item->getExtraData('search_api_solr_document')->getFields()['ss_uuid'];
    if ($uuid) {
      $loaded_entity = $this->getEntityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['uuid' => $uuid]);
    }

    // If there isn't a local version of the entity then use the remote render.
    if (empty($loaded_entity)) {
      $type = $row->_item->getExtraData('search_api_solr_document')->getFields()['ss_type'];

      try {
        return $this->renderRemote($uuid, $type);
      }
      catch (Exception $e) {
        return '';
      }
    }
    elseif (empty($row->_object)) {
      $row->_object = EntityAdapter::createFromEntity(reset($loaded_entity));
    }

    if (!$this->index->isValidDatasource($datasource_id)) {
      $context = [
        '%datasource' => $datasource_id,
        '%view' => $this->view->storage->label(),
      ];
      $this->getLogger()->warning('Item of unknown datasource %datasource returned in view %view.', $context);
      return '';
    }
    // Always use the default view mode if it was not set explicitly in the
    // options.
    $view_mode = 'default';
    $bundle = $this->index->getDatasource($datasource_id)->getItemBundle($row->_object);
    if (isset($this->options['view_modes'][$datasource_id][$bundle])) {
      $view_mode = $this->options['view_modes'][$datasource_id][$bundle];
    }

    try {
      return $this->index->getDatasource($datasource_id)->viewItem($row->_object, $view_mode);
    }
    catch (SearchApiException $e) {
      $this->logException($e);
      return '';
    }
  }

  /**
   * Create a render array for a remote entity.
   *
   * @param string $uuid
   *  The UUID to render.
   * @param string $type
   *   The type of entity to render.
   *
   * @return bool|array
   */
  public function renderRemote($uuid, $type) {
    $node = $this->loadRemote($uuid, $type);

    if (empty($node)) {
      return FALSE;
    }

    // Work out which view mode to use based on node type and View display.
    $view_mode = NULL;
    $map_view = in_array($this->view->current_display, ['events_map', 'reserves_map']);
    if ($map_view) {
      $view_mode = 'gmap_point';
    }
    elseif ($type == 'trust') {
      $view_mode = 'full';
    }
    else {
      $view_mode = 'teaser';
    }

    $render = node_view($node, $view_mode);
    foreach ($node->remoteRenders as $name => $value) {
      $render[$name] = $value;
    }

    return $render;
  }

  /**
   * @param $uuid
   * @param $type
   */
  protected function loadRemote($uuid, $type) {
    $parameters = [
      'type' => $type,
    ];

    $remote_field_values = \Drupal::service('wildlife_search.localapi_field_values')->getFields($uuid);

    if (empty($remote_field_values)) {
      return NULL;
    }
    else {
      $item = $remote_field_values['item'];
      $used_domain = $remote_field_values['used_domain'];

      $remote_renders = [];

      // Some fields require special processing.
      foreach ($item as $name => $value) {
        // If the key includes the word 'raw', do nothing.
        if (strpos($name, 'raw') !== FALSE) {
          continue;
        }
        // Date fields require start and end values to be set.
        elseif (strpos($name, 'date') !== FALSE) {
          $date = explode(':', $value);
          if (!empty($date[0]) && !empty($date[1])) {
            $parameters[$name]['value'] = trim($date[0]);
            $parameters[$name]['end_value'] = trim($date[1]);
          }
          elseif (!empty($date[0])) {
            $parameters[$name]['value'] = trim($date[0]);
            $parameters[$name]['end_value'] = trim($date[0]);
          }
          else {
            $parameters[$name]['value'] = NULL;
            $parameters[$name]['end_value'] = NULL;
          }
        }
        // Render image URLs as images.
        elseif ((strpos($name, 'photos') !== FALSE || strpos($name, 'image') !== FALSE) && strpos($name, 'map_thumb') === FALSE) {
          if (!empty($value)) {
            $remote_renders[$name] = [
              '#theme' => 'image',
              '#style_name' => 'grid_teaser',
              '#uri' => $used_domain . $value,
            ];
          }
        }
        // Link fields are sent as markup.
        elseif (strpos($name, 'link') !== FALSE || strpos($name, 'site_url') !== FALSE) {
          $remote_renders[$name] = [
            '#markup' => html_entity_decode($value),
          ];
        }
        elseif (strpos($name, 'tags') !== FALSE || strpos($name, 'event_type') !== FALSE || strpos($name, 'great_for') !== FALSE) {
          $remote_renders[$name] = [
            '#markup' => html_entity_decode($value),
          ];
        }
        else {
          $entity_decoded_value = html_entity_decode($value);
          $parameters[$name] = htmlspecialchars_decode($entity_decoded_value, ENT_QUOTES);
        }
      }

      $node = Node::create($parameters);
      $node->remoteRenders = $remote_renders;
      $node->remoteObject = $item;
      return $node;
    }
  }
}
