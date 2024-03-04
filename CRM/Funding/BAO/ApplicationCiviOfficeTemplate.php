<?php
declare(strict_types = 1);

use Civi\Core\Event\GenericHookEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// phpcs:disable Generic.Files.LineLength.TooLong
final class CRM_Funding_BAO_ApplicationCiviOfficeTemplate extends CRM_Funding_DAO_ApplicationCiviOfficeTemplate implements EventSubscriberInterface {
// phpcs:enable

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      'civi.afform_admin.metadata' => 'onAfformAdminMetadata',
    ];
  }

  /**
   * Provides Afform metadata about this entity.
   *
   * @see \Civi\AfformAdmin\AfformAdminMeta::getMetadata()
   */
  public static function onAfformAdminMetadata(GenericHookEvent $event): void {
    $entity = 'Funding' . pathinfo(__FILE__, PATHINFO_FILENAME);
    $event->entities[$entity] = [
      'entity' => $entity,
      'label' => $entity,
      'type' => 'primary',
      'defaults' => '{}',
    ];
  }

}
