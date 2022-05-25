<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteGetEvent;
use Civi\Funding\EventSubscriber\Traits\RemoteFundingProgramSubscriberTrait;

class RemoteFundingProgramDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  use RemoteFundingProgramSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingProgram';

  protected const ENTITY_NAME = 'RemoteFundingProgram';

  public function onGet(RemoteGetEvent $event): void {
    parent::onGet($event);

    $event->setRecords(iterator_to_array($this->addPermissionsToRecords($event)));
  }

  private function addPermissionsToRecords(RemoteGetEvent $event): iterable {
    foreach ($event->getRecords() as $record) {
      $record['permissions'] = $this->getRecordPermissions($event, $record);
      foreach ($record['permissions'] as $permission) {
        $record['PERM_' . $permission] = TRUE;
      }

      yield $record;
    }
  }

  private function getRecordPermissions(RemoteGetEvent $event, $record): array {
    // TODO
    return ['dummy'];
  }

}
