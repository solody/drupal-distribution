<?php

namespace Drupal\distribution\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Level entities.
 */
class LevelViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
