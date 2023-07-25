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
use Civi\Funding\Util\TestFileUtil;

/**
 * Decorates a FundingAttachmentManagerInterface and deletes written files on
 * script shutdown.
 */
final class TestAttachmentManager implements FundingAttachmentManagerInterface {

  private FundingAttachmentManagerInterface $attachmentManager;

  public function __construct(FundingAttachmentManagerInterface $attachmentManager) {
    $this->attachmentManager = $attachmentManager;
  }

  /**
   * @inheritDoc
   */
  public function attachFile(
    string $entityTable,
    int $entityId,
    string $filename,
    string $mimeType,
    array $optional = []
  ): AttachmentEntity {
    $attachment = $this->attachmentManager->attachFile($entityTable, $entityId, $filename, $mimeType);
    TestFileUtil::deleteFileOnScriptShutdown($attachment->getPath());

    return $attachment;
  }

  /**
   * @inheritDoc
   */
  public function attachFileUniqueByFileType(
    string $entityTable,
    int $entityId,
    string $fileTypeName,
    string $filename,
    string $mimeType,
    array $optional = []
  ): AttachmentEntity {
    return $this->attachmentManager->attachFileUniqueByFileType(
      $entityTable,
      $entityId,
      $fileTypeName,
      $filename,
      $mimeType,
      $optional,
    );
  }

  /**
   * @inheritDoc
   */
  public function delete(AttachmentEntity $attachment): void {
    $this->attachmentManager->delete($attachment);
  }

  /**
   * @inheritDoc
   */
  public function get(int $id, string $entityTable, int $entityId): ?AttachmentEntity {
    return $this->attachmentManager->get($id, $entityTable, $entityId);
  }

  /**
   * @inheritDoc
   */
  public function getByFileType(string $entityTable, int $entityId, string $fileTypeName): array {
    return $this->attachmentManager->getByFileType($entityTable, $entityId, $fileTypeName);
  }

  /**
   * @inheritDoc
   */
  public function getLastByFileType(string $entityTable, int $entityId, string $fileTypeName): ?AttachmentEntity {
    return $this->attachmentManager->getLastByFileType($entityTable, $entityId, $fileTypeName);
  }

  /**
   * @inheritDoc
   */
  public function has(string $entityTable, int $entityId, string $fileTypeName): bool {
    return $this->attachmentManager->has($entityTable, $entityId, $fileTypeName);
  }

  /**
   * @inheritDoc
   */
  public function update(AttachmentEntity $attachment): void {
    $this->attachmentManager->update($attachment);
  }

}
