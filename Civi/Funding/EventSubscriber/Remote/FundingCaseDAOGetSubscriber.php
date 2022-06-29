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
use Civi\RemoteTools\Event\DAOGetEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetSubscriber;
use Webmozart\Assert\Assert;

final class FundingCaseDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  protected const DAO_ENTITY_NAME = 'FundingCase';

  protected const ENTITY_NAME = 'RemoteFundingCase';

  protected const EVENT_CLASS = FundingDAOGetEvent::class;

  public function onGet(DAOGetEvent $event): void {
    /** @var \Civi\Funding\Event\Remote\FundingDAOGetEvent $event */
    parent::onGet($event);

    $event->setRecords($this->handlePermissions($event->getRecords()));
  }

  /**
   * @param array<array<string, mixed>> $records
   *
   * @return array<array<string, mixed>>
   */
  private function handlePermissions(array $records): array {
    foreach ($records as &$record) {
      Assert::isArray($record['permissions']);
      $record['permissions'] = $this->mergePermissions(
        $this->jsonEncodePermissions($record['permissions'])
      );

      foreach ($record['permissions'] as $permission) {
        $record['PERM_' . $permission] = TRUE;
      }
    }

    return $records;
  }

  /**
   * @param string[] $permissions
   *
   * @return array<string[]>
   */
  private function jsonEncodePermissions(array $permissions): array {
    /** @var array<string[]> $permissions */
    $permissions = array_map('json_decode', $permissions);

    return $permissions;
  }

  /**
   * @param array<string[]> $permissions
   *
   * @return string[]
   */
  private function mergePermissions(array $permissions): array {
    return array_values(array_unique(
      array_reduce($permissions, fn(array $p1, array $p2): array => array_merge($p1, $p2), [])
    ));
  }

}
