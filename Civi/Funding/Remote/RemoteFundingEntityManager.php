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
use Civi\Funding\Api4\Action\FundingContactIdSessionAwareInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class RemoteFundingEntityManager implements RemoteFundingEntityManagerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getById(string $entity, int $id, string $remoteContactId): ?array {
    $params = [
      'where' => [
        ['id', '=', $id],
      ],
    ];

    $okToContinue = FALSE;
    try {
      $contactIdParams = $this->buildContactIdParams($entity, $remoteContactId);
      if ([] !== $contactIdParams || $this->isContactIdSessionAware($entity)) {
        $okToContinue = TRUE;
      }
    }
    catch (NotImplementedException $e) {
      throw new \InvalidArgumentException(
        sprintf('Unknown entity "%s"', $entity), $e->getCode(), $e);
    }

    if (!$okToContinue && !str_starts_with($entity, 'Remote')) {
      try {
        if (!$this->doHasAccess('Remote' . $entity, $id, $remoteContactId)) {
          return NULL;
        }
      }
      catch (NotImplementedException $ignore) {
        // @ignoreException
      }
    }
    else {
      $params += $contactIdParams;
    }

    $result = $this->api4->execute($entity, 'get', $params);
    /** @var array<string, mixed>|null $record */
    $record = $result->first();

    return $record;
  }

  public function hasAccess(string $entity, int $id, string $remoteContactId): bool {
    if (!str_starts_with($entity, 'Remote')) {
      try {
        return $this->doHasAccess('Remote' . $entity, $id, $remoteContactId);
      }
      catch (NotImplementedException $ignore) {
        // @ignoreException
      }
    }

    try {
      return $this->doHasAccess($entity, $id, $remoteContactId);
    }
    catch (NotImplementedException $e) {
      throw new \InvalidArgumentException(
        sprintf('Unknown entity "%s"', $entity), $e->getCode(), $e
      );
    }
  }

  /**
   * @return array{remoteContactId?: string, contactId?: int}
   *
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function buildContactIdParams(string $entity, string $remoteContactId): array {
    $contactIdParams = [];
    if ($this->hasParam($entity, 'remoteContactId')) {
      $contactIdParams['remoteContactId'] = $remoteContactId;
    }

    return $contactIdParams;
  }

  /**
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function doHasAccess(string $entity, int $id, string $remoteContactId): bool {
    $contactIdParams = $this->buildContactIdParams($entity, $remoteContactId);
    $result = $this->api4->execute($entity, 'get', array_merge([
      'select' => ['id'],
      'where' => [
        ['id', '=', $id],
      ],
    ], $contactIdParams));

    return 1 === $result->rowCount;
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function hasParam(string $entityName, string $param): bool {
    return $this->api4->createAction($entityName, 'get')->paramExists($param);
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function isContactIdSessionAware(string $entityName): bool {
    return $this->api4->createAction($entityName, 'get') instanceof FundingContactIdSessionAwareInterface;
  }

}
