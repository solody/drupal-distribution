<?php

/**
 * @file
 * Contains distribution_target.page.inc.
 *
 * Page callback for Target entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Target templates.
 *
 * Default template: distribution_target.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_target(array &$variables) {
  // Fetch Target Entity Object.
  $distribution_target = $variables['elements']['#distribution_target'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
