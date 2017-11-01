<?php

namespace Drupal\wildlife_search;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the language manager service.
 */
class WildlifeSearchServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $modules = $container->getParameter('container.modules');

    if (isset($modules['geocoder'])) {
      // Overrides geocoder to provide a better error message.
      $definition = $container->getDefinition('geocoder');
      $definition->setClass('Drupal\wildlife_search\WildlifeSearchGeocoder');
    }
  }
}
