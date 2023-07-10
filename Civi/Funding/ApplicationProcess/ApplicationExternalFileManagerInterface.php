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
   * records will be deleted new ones created. EntityFile relations will get
   * lost.
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
  public function deleteFile(ExternalFileEntity $externalFile): void;

  /**
   * @phpstan-param array<string> $excludedIdentifiers
   *
   * @throws \CRM_Core_Exception
   */
  public function deleteFiles(int $applicationProcessId, array $excludedIdentifiers): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFile(string $identifier, int $applicationProcessId): ?ExternalFileEntity;

  /**
   * @phpstan-return array<ExternalFileEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getFiles(int $applicationProcessId): array;

}
