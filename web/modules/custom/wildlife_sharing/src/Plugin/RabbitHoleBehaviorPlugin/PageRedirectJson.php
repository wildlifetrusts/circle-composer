<?php

namespace Drupal\wildlife_sharing\Plugin\RabbitHoleBehaviorPlugin;

use Drupal\Core\Entity\Entity;
use Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPlugin\PageRedirect;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to another page.
 *
 * @RabbitHoleBehaviorPlugin(
 *   id = "page_redirect_json",
 *   label = @Translation("Page redirect (allow JSON api)")
 * )
 */
class PageRedirectJson extends PageRedirect {
  /**
   * {@inheritdoc}
   */
  public function performAction(Entity $entity, Response $current_response = NULL) {
    $path = \Drupal::request()->getPathInfo();
    if (strpos($path, '/jsonapi') === 0) {
      return NULL;
    }
    return parent::performAction($entity, $current_response);
  }
}
