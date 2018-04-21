<?php

/**
 * @file
 * Contains distribution_promoter.page.inc.
 *
 * Page callback for Promoter entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Promoter templates.
 *
 * Default template: distribution_promoter.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_promoter(array &$variables) {
  // Fetch Promoter Entity Object.
  $distribution_promoter = $variables['elements']['#distribution_promoter'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
