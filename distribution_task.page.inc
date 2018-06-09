<?php

/**
 * @file
 * Contains distribution_task.page.inc.
 *
 * Page callback for Task entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Task templates.
 *
 * Default template: distribution_task.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_task(array &$variables) {
  // Fetch Task Entity Object.
  $distribution_task = $variables['elements']['#distribution_task'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
