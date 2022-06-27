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

namespace Civi\Funding\Remote;

use Civi\API\Exception\NotImplementedException;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;

final class RemoteFundingEntityManager implements RemoteFundingEntityManagerInterface {

  private static RemoteFundingEntityManager $instance;

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
    self::$instance = $this;
  }

  public static function getInstance(): self {
    return self::$instance ?? new self(Api4::getInstance());
  }

  /**
   * @inheritDoc
   */
  public function getById(string $entity, int $id, string $remoteContactId): ?array {
    $params = ['where' => ['id', '=', $id]];
    $remoteEntityParams = ['remoteContactId' => $remoteContactId];

    if (!str_starts_with($entity, 'Remote')) {
      try {
        if (!$this->doHasAccess('Remote' . $entity, $id, $remoteEntityParams)) {
          return NULL;
        }
      }
      catch (NotImplementedException $ignore) {
        // @ignoreException
      }
    }
    else {
      $params += $remoteEntityParams;
    }

    try {
      $result = $this->api4->execute($entity, 'get', $params);
    }
    catch (NotImplementedException $e) {
      throw new \InvalidArgumentException(
        sprintf('Unknown entity "%s"', $entity), $e->getCode(), $e);
    }

    /** @var array<string, mixed>|null $record */
    $record = $result->getArrayCopy()[0] ?? NULL;

    return $record;
  }

  public function hasAccess(string $entity, int $id, string $remoteContactId): bool {
    $params = [];
    $remoteEntityParams = ['remoteContactId' => $remoteContactId];
    if (!str_starts_with($entity, 'Remote')) {
      $remoteEntity = 'Remote' . $entity;

      try {
        return $this->doHasAccess($remoteEntity, $id, $remoteEntityParams);
      }
      catch (NotImplementedException $ignore) {
        // @ignoreException
      }
    }
    else {
      $params = $remoteEntityParams;
    }

    try {
      return $this->doHasAccess($entity, $id, $params);
    }
    catch (NotImplementedException $e) {
      throw new \InvalidArgumentException(
        sprintf('Unknown entity "%s"', $entity), $e->getCode(), $e
      );
    }
  }

  /**
   * @param string $entity
   * @param int $id
   * @param array<string, mixed> $params
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function doHasAccess(string $entity, int $id, array $params = []): bool {
    $result = $this->api4->execute($entity, 'get', array_merge([
      'select' => ['id'],
      'where' => [
        'id', '=', $id,
      ],
    ], $params));

    return 1 === $result->rowCount;
  }

}
