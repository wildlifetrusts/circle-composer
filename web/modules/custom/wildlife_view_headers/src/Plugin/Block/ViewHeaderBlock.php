<?php

namespace Drupal\wildlife_view_headers\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\wildlife_view_headers\Entity\ViewHeader;

/**
 * Provides a 'ViewHeaderBlock' block.
 *
 * @Block(
 *  id = "view_header_block",
 *  admin_label = @Translation("View header block"),
 * )
 */
class ViewHeaderBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($view_header = $this->findViewHeader()) {
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder('view_header');
      $build['view_header_block'] = $view_builder->view($view_header, 'default');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

  /**
   * Find the correct ViewHeader (if any) for the current request.
   *
   * @return ViewHeader|null
   */
  public static function findViewHeader() {
    $view_header = NULL;

    if ($view_id = \Drupal::routeMatch()->getParameter('view_id')) {
      $view_display_id = \Drupal::routeMatch()->getParameter('display_id');

      $query = \Drupal::entityQuery('view_header');
      $query->condition('view_id', $view_id);
      $query->condition('view_display_id', $view_display_id);
      $entity_id = $query->execute();
    }

    if (empty($entity_id) && $taxonomy_term = \Drupal::routeMatch()->getParameter('taxonomy_term')) {
      $vid = $taxonomy_term->getVocabularyId();

      // On taxonomy term pages, use the Explorer listing header.
      $query = \Drupal::entityQuery('view_header');
      $query->condition('view_id', 'explorer');
      $query->condition('view_display_id', $vid);
      $entity_id = $query->execute();
    }

    if (!empty($entity_id)) {
      $view_header_id = reset($entity_id);
      $view_header = ViewHeader::load($view_header_id);
    }
    return $view_header;
  }
}
