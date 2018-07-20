<?php

namespace Drupal\distribution\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Monthly reward condition item annotation object.
 *
 * @see \Drupal\distribution\Plugin\MonthlyRewardConditionManager
 * @see plugin_api
 *
 * @Annotation
 */
class MonthlyRewardCondition extends Plugin {


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
