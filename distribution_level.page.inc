<?php

/**
 * @file
 * Contains distribution_level.page.inc.
 *
 * Page callback for Level entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Level templates.
 *
 * Default template: distribution_level.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_level(array &$variables) {
  // Fetch Level Entity Object.
  $distribution_level = $variables['elements']['#distribution_level'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
