<?php

namespace Drupal\wildlife_search\Plugin\views\exposed_form;

use Drupal\views\Plugin\views\exposed_form\InputRequired;

/**
 * Class InputRequiredNotEmpty
 *
 * @package Drupal\wildlife_search\Plugin\views\exposed_form
 *
 * @ViewsExposedForm(
 *   id = "input_required_not_empty",
 *   title = @Translation("Input required (not empty)"),
 *   help = @Translation("An exposed form that only renders a view if the form contains user input.")
 * )
 */
class InputRequiredNotEmpty extends InputRequired {

  /**
   * {@inheritdoc}
   */
  protected function exposedFilterApplied() {
    static $cache = NULL;
    if (!isset($cache)) {
      $view = $this->view;
      if (is_array($view->filter) && count($view->filter)) {
        foreach ($view->filter as $filter) {
          if ($filter->isExposed()) {
            $identifier = $filter->options['expose']['identifier'];
            if (!empty($view->getExposedInput()[$identifier])) {
              $cache = TRUE;
              return $cache;
            }
          }
        }
      }
      $cache = FALSE;
    }

    return $cache;
  }
}
