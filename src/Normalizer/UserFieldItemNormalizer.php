<?php

namespace Drupal\distribution\Normalizer;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\distribution\Entity\DistributorInterface;
use Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer;
use Drupal\user\Entity\User;

/**
 * Expands product variations to their referenced entity.
 */
class UserFieldItemNormalizer extends EntityReferenceFieldItemNormalizer {

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    $supported = parent::supportsNormalization($data, $format);

    if ($supported) {
      if ($data instanceof EntityReferenceItem) {
        $entity = $data->get('entity')->getValue();
        if ($entity instanceof User) {
          if ($data->getParent() && $data->getParent()->getParent() && $data->getParent()->getParent()->getValue() instanceof DistributorInterface) {
            return true;
          }
        }
      }
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $field_item */
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if ($entity = $field_item->get('entity')->getValue()) {
      return $this->serializer->normalize($entity, $format, $context);
    }
    return $this->serializer->normalize([], $format, $context);
  }

}
