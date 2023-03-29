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

namespace Civi\Funding\DocumentRender\CiviOffice;

/**
 * Data is added by CiviOfficeDocumentRenderer and is available to token
 * subscribers.
 */
class CiviOfficeContextDataHolder {

  /**
   * @phpstan-var array<string, array<int, array<mixed>>>
   */
  private array $data = [];

  /**
   * @phpstan-param array<mixed> $data
   */
  public function addEntityData(string $entityType, int $entityId, array $data): void {
    $this->data[$entityType][$entityId] = $data;
  }

  /**
   * @phpstan-return array<mixed>
   */
  public function getEntityData(string $entityType, int $entityId): array {
    return $this->data[$entityType][$entityId] ?? [];
  }

  /**
   * @param mixed $default
   *
   * @return mixed
   */
  public function getEntityDataValue(string $entityType, int $entityId, string $key, $default = NULL) {
    return $this->getEntityData($entityType, $entityId)[$key] ?? $default;
  }

  public function removeEntityData(string $entityType, int $entityId): void {
    unset($this->data[$entityType][$entityId]);
  }

}
