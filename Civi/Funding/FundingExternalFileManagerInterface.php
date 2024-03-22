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

namespace Civi\Funding;

use Civi\Funding\Entity\ExternalFileEntity;
use Civi\RemoteTools\Api4\Query\ConditionInterface;

interface FundingExternalFileManagerInterface {

  /**
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   *
   * @throws \CRM_Core_Exception
   */
  public function addFile(
    string $uri,
    string $identifier,
    string $entityName,
    int $entityId,
    ?array $customData = NULL
  ): ExternalFileEntity;

  /**
   * If the URI of an existing file is changed, the corresponding database
   * records will be deleted and new ones created. EntityFile relations will get
   * lost.
   *
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   *
   * @throws \CRM_Core_Exception
   */
  public function addOrUpdateFile(
    string $uri,
    string $identifier,
    string $entityName,
    int $entityId,
    ?array $customData = NULL
  ): ExternalFileEntity;

  /**
   * @throws \CRM_Core_Exception
   */
  public function attachFile(ExternalFileEntity $externalFile, string $entityName, int $entityId): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function deleteFile(ExternalFileEntity $externalFile): void;

  /**
   * @phpstan-param array<string> $excludedIdentifiers
   *
   * @throws \CRM_Core_Exception
   */
  public function deleteFiles(string $entityName, int $entityId, array $excludedIdentifiers): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function detachFile(ExternalFileEntity $externalFile, string $entityName, int $entityId): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFile(string $identifier, string $entityName, int $entityId): ?ExternalFileEntity;

  /**
   * @phpstan-return array<ExternalFileEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getFiles(string $entityName, int $entityId, ?ConditionInterface $condition = NULL): array;

  /**
   * @throws \CRM_Core_Exception
   */
  public function isAttachedToEntityType(ExternalFileEntity $externalFile, string $entityName): bool;

  public function isFileChanged(ExternalFileEntity $externalFile, string $newUri): bool;

  /**
   * @phpstan-param array<int|string, mixed> $customData JSON serializable.
   *
   * @throws \CRM_Core_Exception
   */
  public function updateCustomData(ExternalFileEntity $externalFile, array $customData): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function updateIdentifier(ExternalFileEntity $externalFile, string $identifier): void;

}
