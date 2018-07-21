<?php

namespace Drupal\distribution\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Monthly reward strategy item annotation object.
 *
 * @see \Drupal\distribution\Plugin\MonthlyRewardStrategyManager
 * @see plugin_api
 *
 * @Annotation
 */
class MonthlyRewardStrategy extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
