<?php

namespace Drupal\wildlife_seo;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies the pathauto generator service.
 */
class WildlifeSeoServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $modules = $container->getParameter('container.modules');

    if (isset($modules['pathauto'])) {
      // Overrides pathauto generator so we can update nodes based on taxonomy.
      $definition = $container->getDefinition('pathauto.generator');
      $arguments = $definition->getArguments();
      $arguments[] = new Reference('database');
      $definition->setArguments($arguments);
      $definition->setClass('Drupal\wildlife_seo\WildlifeSeoPathautoGenerator');
    }
  }
}
