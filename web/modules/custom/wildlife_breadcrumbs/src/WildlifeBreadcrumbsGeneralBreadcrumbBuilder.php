<?php

namespace Drupal\wildlife_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Adds the current page title to the breadcrumb.
 *
 * Extend PathBased Breadcrumbs to include the current page title as an unlinked
 * crumb. The module uses the path if the title is unavailable and it excludes
 * all admin paths.
 *
 * {@inheritdoc}
 */
class WildlifeBreadcrumbsGeneralBreadcrumbBuilder extends PathBasedBreadcrumbBuilder {
  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    /** @var Breadcrumb $breadcrumbs */
    $breadcrumbs = parent::build($route_match);
    $request = \Drupal::request();
    $path = trim($this->context->getPathInfo(), '/');
    $path_elements = explode('/', $path);
    $route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT);

    // Add cache contexts.
    $breadcrumbs->addCacheContexts(['url.path']);

    $existing_links = $breadcrumbs->getLinks();

    // Add the page title breadcrumb, only if there is at least one other
    // breadcrumb (usually Home). This will stop the front page having an
    // orphan crumb.
    if (!empty($existing_links) && $route && !$route->getOption('_admin_route')) {
      $title = $this->titleResolver->getTitle($request, $route);
      if (!isset($title)) {
        // Fallback to using the raw path component as the title if the
        // route is missing a _title or _title_callback attribute.
        $title = str_replace(array('-', '_'), ' ', Unicode::ucfirst(end($path_elements)));
      }
      $breadcrumbs->addLink(Link::createFromRoute($title, '<none>'));
    }
    return $breadcrumbs;
  }
}
