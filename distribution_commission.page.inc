<?php

/**
 * @file
 * Contains distribution_commission.page.inc.
 *
 * Page callback for Commission entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Commission templates.
 *
 * Default template: distribution_commission.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_commission(array &$variables) {
  // Fetch Commission Entity Object.
  $distribution_commission = $variables['elements']['#distribution_commission'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
