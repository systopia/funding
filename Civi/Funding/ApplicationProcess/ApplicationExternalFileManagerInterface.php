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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\ExternalFileEntity;

interface ApplicationExternalFileManagerInterface {

  /**
   * If the URI of an existing file is changed, the corresponding database
   * records will be deleted and new ones created. EntityFile relations will get
   * lost. If such a file is used in a snapshot, its identifier will be changed
   * instead, and it will be detached from the application process.
   *
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   *
   * @throws \CRM_Core_Exception
   */
  public function addOrUpdateFile(
    string $uri,
    string $identifier,
    int $applicationProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity;

  /**
   * @throws \CRM_Core_Exception
   */
  public function attachFileToSnapshot(ExternalFileEntity $externalFile, int $snapshotId): void;

  /**
   * Deletes all files attached to the application process, if the identifier is
   * not part of the excluded ones. In case a file is used in a snapshot, its
   * identifier will be changed instead, and it will be detached from the
   * application process
   *
   * @phpstan-param array<string> $excludedIdentifiers
   *   May already contain the identifier prefix that was added to the entities'
   *   identifier.
   *
   * @throws \CRM_Core_Exception
   */
  public function deleteFiles(int $applicationProcessId, array $excludedIdentifiers): void;

  /**
   * @param string $identifier
   *   May already contain the identifier prefix that was added to the entity's
   *   identifier.
   *
   * @throws \CRM_Core_Exception
   */
  public function getFile(string $identifier, int $applicationProcessId): ?ExternalFileEntity;

  /**
   * @phpstan-return array<string, ExternalFileEntity>
   *   The key contains the identifier used when the file was added. The
   *   identifier of the value object has an additional prefix.
   *
   * @throws \CRM_Core_Exception
   */
  public function getFiles(int $applicationProcessId): array;

  /**
   * @phpstan-return array<ExternalFileEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getFilesAttachedToSnapshot(int $snapshotId): array;

  /**
   * Restores the identifier if it was changed to a snapshot identifier and
   * (re-)attaches the file to the application process.
   *
   * @param \Civi\Funding\Entity\ExternalFileEntity $externalFile
   *   A file attached to a snapshot.
   *
   * @throws \CRM_Core_Exception
   */
  public function restoreFileSnapshot(ExternalFileEntity $externalFile, int $applicationProcessId): void;

}
