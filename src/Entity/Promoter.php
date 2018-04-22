<?php

namespace Drupal\distribution\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Promoter entity.
 *
 * @ingroup distribution
 *
 * @ContentEntityType(
 *   id = "distribution_promoter",
 *   label = @Translation("Promoter"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\distribution\PromoterListBuilder",
 *     "views_data" = "Drupal\distribution\Entity\PromoterViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\distribution\Form\PromoterForm",
 *       "add" = "Drupal\distribution\Form\PromoterForm",
 *       "edit" = "Drupal\distribution\Form\PromoterForm",
 *       "delete" = "Drupal\distribution\Form\PromoterDeleteForm",
 *     },
 *     "access" = "Drupal\distribution\PromoterAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\distribution\PromoterHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "distribution_promoter",
 *   admin_permission = "administer promoter entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/distribution/distribution_promoter/{distribution_promoter}",
 *     "add-form" = "/admin/distribution/distribution_promoter/add",
 *     "edit-form" = "/admin/distribution/distribution_promoter/{distribution_promoter}/edit",
 *     "delete-form" = "/admin/distribution/distribution_promoter/{distribution_promoter}/delete",
 *     "collection" = "/admin/distribution/distribution_promoter",
 *   },
 *   field_ui_base_route = "distribution_promoter.settings"
 * )
 */
class Promoter extends ContentEntityBase implements PromoterInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedTime($timestamp)
    {
        $this->set('created', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDistributor()
    {
        return $this->get('distributor_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['distributor_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('推广者'))
            ->setSetting('target_type', 'distribution_distributor')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('接受推广的用户'))
            ->setSetting('target_type', 'user')
            ->setSetting('handler', 'default')
            ->setDisplayOptions('view', [
                'label' => 'inline',
                'type' => 'entity_reference_label'
            ]);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        return $fields;
    }

}
