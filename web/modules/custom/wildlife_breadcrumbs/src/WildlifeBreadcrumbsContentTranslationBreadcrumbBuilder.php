<?php

namespace Drupal\wildlife_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\system\PathBasedBreadcrumbBuilder;

/**
 * Content Translation breadcrumbs.
 *
 * {@inheritdoc}
 */
class WildlifeBreadcrumbsContentTranslationBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Check if the route is an entity content translation page.
    if ($entity_type_id = $route_match->getParameter('entity_type_id')) {
      return $route_match->getRouteName() == 'entity.' . $entity_type_id . '.content_translation_add';
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    /** @var Breadcrumb $breadcrumbs */
    $existing_breadcrumbs = parent::build($route_match);
    $existing_links = $existing_breadcrumbs->getLinks();

    $breadcrumbs = new Breadcrumb();
    $entity_type_id = $route_match->getParameter('entity_type_id');

    foreach ($existing_links as $link) {
      $route_name = $link->getUrl()->getRouteName();
      $route_params = $link->getUrl()->getRouteParameters();
      if ($route_name == 'entity.' . $entity_type_id . '.content_translation_add') {
        if ($route_params['source'] instanceof LanguageInterface) {
          $breadcrumbs->addLink($link);
        }
      }
      else {
        $breadcrumbs->addLink($link);
      }
    }

    // Add cache contexts.
    $breadcrumbs->addCacheContexts(['url.path']);

    return $breadcrumbs;
  }
}
