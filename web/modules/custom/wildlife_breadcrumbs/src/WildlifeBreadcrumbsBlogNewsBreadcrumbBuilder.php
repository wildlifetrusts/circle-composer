<?php

namespace Drupal\wildlife_breadcrumbs;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\Entity\Term;

/**
 * Blog and News views breadcrumbs.
 *
 * {@inheritdoc}
 */
class WildlifeBreadcrumbsBlogNewsBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $blog_news_view_routes = [
      'view.blog.listing',
      'view.blog.by_tag',
      'view.blog.by_category',
      'view.news.listing',
      'view.news.by_tag',
      'view.news.by_category',
    ];

    $is_blog_news_view = in_array($route_match->getRouteName(), $blog_news_view_routes);
    $is_blog_node = $route_match->getRouteName() == 'entity.node.canonical' && $route_match->getParameter('node')->getType() == 'blog';

    return $is_blog_news_view || $is_blog_node;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $route_name = $route_match->getRouteName();

    switch ($route_name) {
      case 'view.news.listing':
      case 'view.news.by_tag':
      case 'view.news.by_category':
        $content_type = 'news';
        $view_human_name = $this->t('News');

        break;

      default:
        $content_type = 'blog';
        $view_human_name = $this->t('Blog');
    }

    $is_landing_page = TRUE;
    $breadcrumb_links = [];

    // Author blog view.
    if ($route_name == 'view.blog.listing' && $author_blog_id = $route_match->getParameter('arg_0')) {
      $is_landing_page = FALSE;
      $author_name = $this->getAuthorName($author_blog_id);

      if ($author_name) {
        $breadcrumb_links[] = Link::createFromRoute($author_name, '<none>');
      }
    }

    // Author blog node.
    if ($route_name == 'entity.node.canonical') {
      $is_landing_page = FALSE;
      /** @var \Drupal\node\Entity\Node $node */
      $node = $route_match->getParameter('node');

      if (!$node->get('field_blog_author')->isEmpty()) {
        /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $author_field */
        $author_field = $node->get('field_blog_author');
        /** @var \Drupal\wildlife_author\Entity\Author $author */
        $author = $author_field->referencedEntities()[0];
        $author_blog_id = $author->get('blog_id')->getString();
        $author_name = $this->getAuthorName($author_blog_id);

        if ($author_name) {
          $breadcrumb_links[] = Link::createFromRoute($author_name, 'view.blog.listing', ['arg_0' => $author_blog_id]);
        }
      }

      $breadcrumb_links[] = Link::createFromRoute($node->getTitle(), '<none>');
    }

    // Category/Tag news or blog.
    if (in_array($route_name, ['view.' . $content_type . '.by_category', 'view.' . $content_type . '.by_tag']) && $route_match->getParameter('arg_0')) {
      $is_landing_page = FALSE;
      $argument = $route_match->getParameter('arg_0');
      $path_alias = '';

      switch ($route_name) {
        case 'view.' . $content_type . '.by_category':
          $path_alias = '/' . $content_type . '-category/' . $argument;
          break;

        case 'view.' . $content_type . '.by_tag':
          $path_alias = '/tag/' . $argument;
          break;
      }

      $system_path = \Drupal::service('path.alias_manager')->getPathByAlias($path_alias);
      $term_url_parts = explode('/', $system_path);

      // Hopefully, get the term name form an actual taxonomy object, but if not
      // fallback to a basic version instead.
      if ($term_url_parts[1] == 'taxonomy' && $term_url_parts[2] == 'term') {
        $tid = $term_url_parts[3];
        $term = Term::load($tid);
        $term_name = $term->getName();
      }
      else {
        $term_name = str_replace(array('-', '_'), ' ', Unicode::ucfirst($argument));
      }

      $breadcrumb_links[] = Link::createFromRoute($term_name, '<none>');
    }

    // Add the home and landing page items to the breadcrumbs.
    $landing_breadcrumb_route = $is_landing_page ? '<none>' : 'view.' . $content_type . '.listing';
    array_unshift($breadcrumb_links, Link::createFromRoute($view_human_name, $landing_breadcrumb_route));
    array_unshift($breadcrumb_links, Link::createFromRoute($this->t('Home'), '<front>'));

    $breadcrumbs = new Breadcrumb();
    $breadcrumbs->addCacheContexts(['url.path']);

    foreach ($breadcrumb_links as $link) {
      $breadcrumbs->addLink($link);
    }

    return $breadcrumbs;
  }

  /**
   * Get the Author name from an author blog id.
   *
   * @param string $author_blog_id
   *   The Author "blog_id" property value.
   * @return bool|string
   *   The Author's name, or false if no author exists for the given blog_id.
   */
  private function getAuthorName($author_blog_id) {
    $author_name = FALSE;
    $entities = \Drupal::entityTypeManager()->getStorage('author')->loadByProperties(['blog_id' => $author_blog_id]);
    $author = ($entities) ? reset($entities) : NULL;

    if ($author) {
      $author_name = $author->getName();
    }

    return $author_name;
  }
}
