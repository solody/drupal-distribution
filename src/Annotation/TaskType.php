<?php

namespace Drupal\distribution\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Task type item annotation object.
 *
 * @see \Drupal\distribution\Plugin\TaskTypeManager
 * @see plugin_api
 *
 * @Annotation
 */
class TaskType extends Plugin {


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
