<?php

namespace Drupal\wildlife_breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;

/**
 * Content Preview breadcrumbs.
 *
 * {@inheritdoc}
 */
class WildlifeBreadcrumbsContentPreviewBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Check if the route is a node preview page.
    return $route_match->getRouteName() == 'entity.node.preview';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumbs = new Breadcrumb();

    $breadcrumbs->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    /** @var Node $node_preview */
    $node_preview = $route_match->getParameter('node_preview');

    $breadcrumbs->addLink(Link::createFromRoute($node_preview->getTitle(), '<none>'));
    $breadcrumbs->addLink(Link::createFromRoute($this->t('Preview'), '<none>'));

    // Add cache contexts.
    $breadcrumbs->addCacheContexts(['url.path']);

    return $breadcrumbs;
  }
}
