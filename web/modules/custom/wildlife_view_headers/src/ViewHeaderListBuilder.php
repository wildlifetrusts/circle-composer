<?php

namespace Drupal\wildlife_view_headers;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use Drupal\views\Views;

/**
 * Defines a class to build a listing of View header entities.
 *
 * @ingroup wildlife_view_headers
 */
class ViewHeaderListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('View header ID');
    $header['name'] = $this->t('Views page');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\wildlife_view_headers\Entity\ViewHeader */
    $row['id'] = $entity->id();
    $view_id = $entity->get('view_id')->getString();
    $view_display_id = $entity->get('view_display_id')->getString();
    $route_name = 'view.' . $view_id . '.' . $view_display_id;
    $route_provider = \Drupal::service('router.route_provider');
    $route_exists = count($route_provider->getRoutesByNames([$route_name])) === 1;

    if ($route_exists) {
      $route = $route_provider->getRouteByName($route_name);
      $view_url = Url::fromRoute($route_name, $route->getOption('_view_argument_map'));
      $row['name'] = Link::fromTextAndUrl($entity->label(), $view_url);
    }
    else {
      $row['name'] = $entity->label();
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no @labels yet.', ['@label' => $this->entityType->getLabel()]);

    return $build;
  }
}
