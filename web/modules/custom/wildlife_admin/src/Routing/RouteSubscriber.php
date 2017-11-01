<?php

/**
 * @file
 * Contains \Drupal\wildlife_admin\Routing\RouteSubscriber.
 */

namespace Drupal\wildlife_admin\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Wildlife Admin routes.
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
    // Add a better permission for Structure landing page.
    if ($route = $collection->get('system.admin_structure')) {
      $route->setRequirements(array('_permission' => 'administer structure'));
    }

    // Add a better permission for Configuration landing page.
    if ($route = $collection->get('system.admin_config')) {
      $route->setRequirements(array('_permission' => 'administer configuration'));
    }

    // Add a better permission for Site Settings.
    if ($route = $collection->get('system.site_information_settings')) {
      $route->setRequirements(array('_permission' => 'administer site settings'));
    }

    // Add a better permission for Configuration sub-pages.
    $configPageAccessRoutes = [
      'user.admin_index',
      'system.admin_config_content',
      'system.admin_config_development',
      'system.admin_config_media',
      'system.admin_config_workflow',
    ];

    $this->assignPermissionsToMultipleRoutes($configPageAccessRoutes, 'access all configuration pages', $collection);

    // Sitemap pages.
    $sitemapPageAccessRoutes = [
      'simple_sitemap.settings',
      'simple_sitemap.settings_entities',
      'simple_sitemap.settings_custom',
    ];

    $this->assignPermissionsToMultipleRoutes($sitemapPageAccessRoutes, 'administer sitemap settings admin section', $collection);

    // Update the Help route.
    if ($route = $collection->get('help.main')) {
      $route->setDefaults(array(
        '_title' => 'Help',
        '_controller' => '\Drupal\wildlife_admin\Controller\WildlifeAdminHelp::main',
      ));
    }
  }

  /**
   * Helper function to assign a permission to multiple routes.
   *
   * @param $routes
   *   An array of route names.
   * @param $permission
   *   The string of the permission.
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function assignPermissionsToMultipleRoutes($routes, $permission, &$collection) {
    foreach ($routes as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->setRequirements(array('_permission' => $permission));
      }
    }
  }

}
