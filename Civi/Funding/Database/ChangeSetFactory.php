<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Database;

use Civi\Core\Event\PreEvent;
use Civi\Funding\Database\Util\EntityNameUtil;
use Civi\RemoteTools\Api4\Api4Interface;

final class ChangeSetFactory {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-param array<string>|null $fieldNames
   *   Limits the change set to those field names, if given.
   *
   * @phpstan-return array<string, array{0: mixed, 1: mixed}>
   *   Mapping of field name to old (index 0) and new (index 1) value. For
   *   create and delete action an empty array is returned.
   *
   * @throws \CRM_Core_Exception
   */
  public function createChangeSetForPreEvent(PreEvent $event, ?array $fieldNames = NULL): array {
    if (NULL === $event->id || 'delete' === $event->action) {
      return [];
    }

    $entityName = EntityNameUtil::getEntityNameForEventEntity($event->entity);

    return $this->createChangeSet($entityName, (int) $event->id, $event->params, $fieldNames);
  }

  /**
   * @phpstan-param array<string, mixed> $values
   * @phpstan-param array<string>|null $fieldNames
   *   Limits the change set to those field names, if given.
   *
   * @phpstan-return array<string, array{0: mixed, 1: mixed}>
   *   Mapping of field name to old (index 0) and new (index 1) value. For
   *   create action an empty array is returned.
   *
   * @throws \CRM_Core_Exception
   */
  public function createChangeSet(string $entityName, int $id, array $values, ?array $fieldNames = NULL): array {
    if (NULL === $fieldNames) {
      $fieldNames = array_keys($values);
    }
    else {
      $fieldNames = array_intersect($fieldNames, array_keys($values));
    }
    if ([] === $fieldNames) {
      return [];
    }

    $oldValues = $this->api4->execute($entityName, 'get', [
      'select' => $fieldNames,
      'where' => [['id', '=', $id]],
      'checkPermissions' => FALSE,
    ])->single();

    $changeSet = [];
    foreach ($fieldNames as $fieldName) {
      // @phpstan-ignore notEqual.notAllowed
      if ($oldValues[$fieldName] != $values[$fieldName]) {
        $changeSet[$fieldName] = [$oldValues[$fieldName], $values[$fieldName]];
      }
    }

    return $changeSet;
  }

}
