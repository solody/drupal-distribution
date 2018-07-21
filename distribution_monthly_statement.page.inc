<?php

/**
 * @file
 * Contains distribution_monthly_statement.page.inc.
 *
 * Page callback for Monthly statement entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Monthly statement templates.
 *
 * Default template: distribution_monthly_statement.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_monthly_statement(array &$variables) {
  // Fetch MonthlyStatement Entity Object.
  $distribution_monthly_statement = $variables['elements']['#distribution_monthly_statement'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
