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

use Civi\Funding\Entity\AttachmentEntity;

interface FundingAttachmentManagerInterface {

  /**
   * @phpstan-param array{
   *   file_type_id?: int,
   *   name?: string,
   *   description?: string,
   *   created_id?: int,
   * } $optional
   *  "name" defaults to basename of $filename.
   *
   * @throws \CRM_Core_Exception
   */
  public function attachFile(
    string $entityTable,
    int $entityId,
    string $filename,
    string $mimeType,
    array $optional = []
  ): AttachmentEntity;

  /**
   * @throws \CRM_Core_Exception
   */
  public function delete(AttachmentEntity $attachment): void;

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id, string $entityTable, int $entityId): ?AttachmentEntity;

  /**
   * @throws \CRM_Core_Exception
   */
  public function getLastByFileType(string $entityTable, int $entityId, int $fileTypeId): ?AttachmentEntity;

  /**
   * @return bool
   *   TRUE if at least one file with the given file type is attached.
   *
   * @throws \CRM_Core_Exception
   */
  public function has(string $entityTable, int $entityId, int $fileTypeId): bool;

  /**
   * @throws \CRM_Core_Exception
   */
  public function update(AttachmentEntity $attachment): void;

}
