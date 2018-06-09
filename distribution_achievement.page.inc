<?php

/**
 * @file
 * Contains distribution_achievement.page.inc.
 *
 * Page callback for Achievement entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Achievement templates.
 *
 * Default template: distribution_achievement.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_distribution_achievement(array &$variables) {
  // Fetch Achievement Entity Object.
  $distribution_achievement = $variables['elements']['#distribution_achievement'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
