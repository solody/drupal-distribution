<?php

namespace Drupal\distribution;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Promoter entities.
 *
 * @ingroup distribution
 */
class PromoterListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Promoter ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\distribution\Entity\Promoter */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.distribution_promoter.edit_form',
      ['distribution_promoter' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
