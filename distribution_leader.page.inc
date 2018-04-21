<?php

/**
 * @file
 * Contains distribution_leader.page.inc.
 *
 * Page callback for Leader entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Leader templates.
 *
 * Default template: distribution_leader.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_leader(array &$variables) {
  // Fetch Leader Entity Object.
  $distribution_leader = $variables['elements']['#distribution_leader'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
