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

namespace Civi\Funding\ClearingProcess;

use Civi\Funding\Entity\ExternalFileEntity;

interface ClearingExternalFileManagerInterface {

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
    int $clearingProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity;

  /**
   * @param string $identifier
   *   May already contain the identifier prefix that was added to the entity's
   *   identifier.
   *
   * @throws \CRM_Core_Exception
   */
  public function getFile(string $identifier, int $clearingProcessId): ?ExternalFileEntity;

  /**
   * @phpstan-return array<string, ExternalFileEntity>
   *   The key contains the identifier used when the file was added. The
   *   identifier of the value object has an additional prefix.
   *
   * @throws \CRM_Core_Exception
   */
  public function getFiles(int $clearingProcessId): array;

}
