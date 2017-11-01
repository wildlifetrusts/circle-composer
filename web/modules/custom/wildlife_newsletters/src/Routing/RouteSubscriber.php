<?php

/**
 * @file
 * Contains \Drupal\wildlife_newsletters\Routing\RouteSubscriber.
 */

namespace Drupal\wildlife_newsletters\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Wildlife Newsletter routes.
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
    $mailchimp_routes = [
      'mailchimp_signup.admin',
      'mailchimp_signup.add',
      'entity.mailchimp_signup.edit_form',
      'entity.mailchimp_signup.delete_form',
    ];

    $permission = 'administer mailchimp sign up forms';

    foreach ($mailchimp_routes as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->setRequirements(array('_permission' => $permission));
      }
    }
  }
}
