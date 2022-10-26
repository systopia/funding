<?php

use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CRM_Funding_BAO_FundingRecipientContactRelation extends CRM_Funding_DAO_FundingRecipientContactRelation implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'civi.afform_admin.metadata' => 'afformAdminMetadata',
    ];
  }

  /**
   * Provides Afform metadata about this entity.
   *
   * @see \Civi\AfformAdmin\AfformAdminMeta::getMetadata().
   */
  public static function afformAdminMetadata(GenericHookEvent $event): void {
    $entity = pathinfo(__FILE__, PATHINFO_FILENAME);
    $event->entities[$entity] = [
      'entity' => $entity,
      'label' => $entity,
      'icon' => NULL,
      'type' => 'primary',
      'defaults' => '{}',
    ];
  }

}
