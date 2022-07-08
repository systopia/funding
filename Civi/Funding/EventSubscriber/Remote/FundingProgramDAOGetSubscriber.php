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

use Civi\Funding\Event\Remote\FundingDAOGetEvent;
use Civi\Funding\EventSubscriber\Remote\Traits\FundingProgramSubscriberTrait;
use Civi\RemoteTools\Event\DAOGetEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetSubscriber;

class FundingProgramDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  use FundingProgramSubscriberTrait;

  protected const DAO_ENTITY_NAME = 'FundingProgram';

  protected const ENTITY_NAME = 'RemoteFundingProgram';

  protected const EVENT_CLASS = FundingDAOGetEvent::class;

  public function onGet(DAOGetEvent $event): void {
    /** @var \Civi\Funding\Event\Remote\FundingDAOGetEvent $event */
    parent::onGet($event);

    /** @var array<array<string, mixed>> $records */
    $records = iterator_to_array($this->addPermissionsToRecords($event));
    $event->setRecords($records);
  }

  /**
   * @param \Civi\Funding\Event\Remote\FundingDAOGetEvent $event
   *
   * @return iterable<array<string, mixed>>
   */
  private function addPermissionsToRecords(FundingDAOGetEvent $event): iterable {
    foreach ($event->getRecords() as $record) {
      $record['permissions'] = $this->getRecordPermissions($event, $record);
      foreach ($record['permissions'] as $permission) {
        $record['PERM_' . $permission] = TRUE;
      }

      yield $record;
    }
  }

  /**
   * @param \Civi\Funding\Event\Remote\FundingDAOGetEvent $event
   * @param array<string, mixed> $record
   *
   * @return string[]
   */
  private function getRecordPermissions(FundingDAOGetEvent $event, array $record): array {
    // TODO
    return ['dummy'];
  }

}