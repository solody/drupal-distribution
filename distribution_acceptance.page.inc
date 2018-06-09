<?php

/**
 * @file
 * Contains distribution_acceptance.page.inc.
 *
 * Page callback for Acceptance entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Acceptance templates.
 *
 * Default template: distribution_acceptance.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_acceptance(array &$variables) {
  // Fetch Acceptance Entity Object.
  $distribution_acceptance = $variables['elements']['#distribution_acceptance'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
