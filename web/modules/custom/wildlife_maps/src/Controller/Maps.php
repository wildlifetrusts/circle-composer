<?php

namespace Drupal\wildlife_maps\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\wildlife_search\Plugin\views\row\RemoteSearchApiRow;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Maps.
 *
 *  Returns responses for Maps related routes..
 *
 * @package Drupal\wildlife_maps\Controller
 */
class Maps extends ControllerBase {

  /**
   * @param string $uuid
   *   The UUID of the node to load.
   * @param string $data_type
   *   The bundle type of the node being loaded.
   *
   * @return Response
   */
  public function renderMapNode($uuid, $data_type) {
    $entity_type = 'node';
    $view_mode = 'map';

    $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $node = $storage->loadByProperties(['uuid' => $uuid]);
    // If there isn't a local version of the entity then use the remote render.
    if (empty($node)) {
      $build = $this->renderRemote($uuid, $data_type);
    }
    else {
      $node = reset($node);
      $build = $view_builder->view($node, $view_mode);
    }

    return new Response(render($build));
  }

  /**
   * Create a render array for a remote entity.
   *
   * @param string $uuid
   *  The UUID to render.
   * @param string $type
   *   The type of entity to render.
   *
   * @return \Drupal\node\NodeInterface
   */
  public function renderRemote($uuid, $type) {
    return $this->loadRemote($uuid, $type);
  }

  /**
   * @param $uuid
   * @param $type
   *
   * @return \Drupal\node\NodeInterface
   */
  protected function loadRemote($uuid, $type) {
    $config_domains = \Drupal::config('wildlife_sharing.settings')->get('domains');

    foreach ($config_domains as $config_domain) {
      $http_client = \Drupal::service('http_client');
      $file_contents = $http_client->get($config_domain['url'] . '/localapi/' . $uuid);
      // If we received a valid response, stop searching.
      if ($file_contents->getStatusCode() == '200') {
        break;
      }
    }

    // If we didn't find a valid file then stop processing.
    if (empty($file_contents)) {
      return NULL;
    }

    $parsed = \GuzzleHttp\json_decode($file_contents->getBody());

    return !empty($parsed) ? $parsed[0]->rendered_entity : NULL;
  }
}
