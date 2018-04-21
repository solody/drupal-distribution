<?php

namespace Drupal\distribution\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Commission type item annotation object.
 *
 * @see \Drupal\distribution\Plugin\CommissionTypeManager
 * @see plugin_api
 *
 * @Annotation
 */
class CommissionType extends Plugin {


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
