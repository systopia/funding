<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Event\Remote\FundingDAOGetEvent;
use Civi\Funding\Event\Remote\FundingProgram\PermissionsGetEvent;
use Civi\Funding\EventSubscriber\Remote\Traits\FundingProgramSubscriberTrait;
use Civi\RemoteTools\Event\DAOGetEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetSubscriber;

class FundingProgramDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  use FundingProgramSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingProgram';

  protected const ENTITY_NAME = 'RemoteFundingProgram';

  protected const EVENT_CLASS = FundingDAOGetEvent::class;

  public function onGet(DAOGetEvent $event, string $eventName, CiviEventDispatcher $eventDispatcher): void {
    /** @var \Civi\Funding\Event\Remote\FundingDAOGetEvent $event */
    parent::onGet($event, $eventName, $eventDispatcher);

    /** @var array<array<string, mixed>> $records */
    $records = iterator_to_array($this->addPermissionsToRecords($event, $eventDispatcher));
    $event->setRowCount(count($records));
    $event->setRecords($records);
  }

  /**
   * @param \Civi\Funding\Event\Remote\FundingDAOGetEvent $event
   * @param \Civi\Core\CiviEventDispatcher $eventDispatcher
   *
   * @return iterable<array<string, mixed>>
   */
  private function addPermissionsToRecords(FundingDAOGetEvent $event, CiviEventDispatcher $eventDispatcher): iterable {
    /** @var array<string, mixed>&array{id: int} $record */
    foreach ($event->getRecords() as $record) {
      $record['permissions'] = $this->getRecordPermissions($event, $record, $eventDispatcher);
      if (NULL === $record['permissions']) {
        continue;
      }
      foreach ($record['permissions'] as $permission) {
        $record['PERM_' . $permission] = TRUE;
      }

      yield $record;
    }
  }

  /**
   * @param \Civi\Funding\Event\Remote\FundingDAOGetEvent $event
   * @param array{id: int} $record
   * @param \Civi\Core\CiviEventDispatcher $eventDispatcher
   *
   * @return string[]|null
   */
  private function getRecordPermissions(FundingDAOGetEvent $event, array $record,
    CiviEventDispatcher $eventDispatcher
  ): ?array {
    $permissionsGetEvent = new PermissionsGetEvent($record['id'], $event->getContactId());
    $eventDispatcher->dispatch(PermissionsGetEvent::class, $permissionsGetEvent);

    return $permissionsGetEvent->getPermissions();
  }

}
