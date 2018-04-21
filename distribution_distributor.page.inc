<?php

/**
 * @file
 * Contains distribution_distributor.page.inc.
 *
 * Page callback for Distributor entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Distributor templates.
 *
 * Default template: distribution_distributor.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_distributor(array &$variables) {
  // Fetch Distributor Entity Object.
  $distribution_distributor = $variables['elements']['#distribution_distributor'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
