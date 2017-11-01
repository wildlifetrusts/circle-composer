<?php

/**
 * @file
 * Contains \Drupal\wildlife_footer_blocks\Routing\RouteSubscriber.
 */

namespace Drupal\wildlife_footer_blocks\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Wildlife Footer Blocks routes.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER] = 'onAlterRoutes';
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Add a better permission for Block pages.
    if ($route = $collection->get('block.admin_display')) {
      $route->setRequirements(array('_permission' => 'administer all block pages'));
    }
    if ($route = $collection->get('entity.block_content_type.collection')) {
      $route->setRequirements(array('_permission' => 'administer all block pages'));
    }
  }

}
