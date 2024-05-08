<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\Funding\Entity\ExternalFileEntity;

interface ClearingExternalFileManagerInterface {

  /**
   * If the URI of an existing file is changed, the corresponding database
   * records will be deleted and new ones created. EntityFile relations will get
   * lost.
   *
   * @phpstan-param AbstractClearingItemEntity<array<string, mixed>>|null $clearingItem
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   *
   * @throws \CRM_Core_Exception
   */
  public function addOrUpdateFile(
    string $uri,
    ?AbstractClearingItemEntity $clearingItem,
    int $clearingProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity;

  /**
   * @phpstan-param AbstractClearingItemEntity<array<string, mixed>> $clearingItem
   *
   * @throws \CRM_Core_Exception
   */
  public function deleteFileByClearingItem(AbstractClearingItemEntity $clearingItem): void;

  /**
   * @phpstan-param AbstractClearingItemEntity<array<string, mixed>> $clearingItem
   *
   * @throws \CRM_Core_Exception
   */
  public function getFile(AbstractClearingItemEntity $clearingItem): ?ExternalFileEntity;

  /**
   * @phpstan-return list<ExternalFileEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getFiles(int $clearingProcessId): array;

}
