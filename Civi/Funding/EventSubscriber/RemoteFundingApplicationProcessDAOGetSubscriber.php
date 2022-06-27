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

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Civi\RemoteTools\Event\DAOGetEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetSubscriber;

final class RemoteFundingApplicationProcessDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  protected const DAO_ENTITY_NAME = 'FundingApplicationProcess';

  protected const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  protected const EVENT_CLASS = RemoteFundingDAOGetEvent::class;

  public function onGet(DAOGetEvent $event): void {
    /** @var \Civi\Funding\Event\RemoteFundingDAOGetEvent $event */
    parent::onGet($event);

    /** @var array<array<string, mixed>> $records */
    $records = iterator_to_array($this->addPermissionsToRecords($event));
    $event->setRecords($records);
  }

  /**
   * @param \Civi\Funding\Event\RemoteFundingDAOGetEvent $event
   *
   * @return iterable<array<string, mixed>>
   */
  private function addPermissionsToRecords(RemoteFundingDAOGetEvent $event): iterable {
    foreach ($event->getRecords() as $record) {
      $record['permissions'] = $this->getRecordPermissions($event, $record);
      foreach ($record['permissions'] as $permission) {
        $record['PERM_' . $permission] = TRUE;
      }

      yield $record;
    }
  }

  /**
   * @param \Civi\Funding\Event\RemoteFundingDAOGetEvent $event
   * @param array<string, mixed> $record
   *
   * @return string[]
   */
  private function getRecordPermissions(RemoteFundingDAOGetEvent $event, array $record): array {
    // TODO
    return ['dummy'];
  }

}
