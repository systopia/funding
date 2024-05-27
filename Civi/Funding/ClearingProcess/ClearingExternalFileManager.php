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

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\ExternalFile\FundingExternalFileManagerInterface;
use Civi\Funding\Util\Uuid;

final class ClearingExternalFileManager implements ClearingExternalFileManagerInterface {

  private FundingExternalFileManagerInterface $externalFileManager;

  public function __construct(FundingExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @inheritDoc
   */
  public function addOrUpdateFile(
    string $uri,
    ?AbstractClearingItemEntity $clearingItem,
    int $clearingProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $externalFile = NULL === $clearingItem ? NULL : $this->getFile($clearingItem);
    if (NULL === $externalFile) {
      $identifier = $this->getIdentifierPrefix($clearingProcessId) . Uuid::generateRandom();
    }
    else {
      $identifier = $externalFile->getIdentifier();
    }

    if (NULL !== $externalFile
      && $this->externalFileManager->isFileChanged($externalFile, $uri)) {
      $this->externalFileManager->deleteFile($externalFile);

      return $this->externalFileManager->addFile(
        $uri,
        $identifier,
        FundingClearingProcess::getEntityName(),
        $clearingProcessId,
        $customData
      );
    }

    return $this->externalFileManager->addOrUpdateFile(
      $uri,
      $identifier,
      FundingClearingProcess::getEntityName(),
      $clearingProcessId,
      $customData,
    );
  }

  public function deleteFileByClearingItem(AbstractClearingItemEntity $clearingItem): void {
    $externalFile = $this->getFile($clearingItem);
    if (NULL !== $externalFile) {
      $this->externalFileManager->deleteFile($externalFile);
    }
  }

  /**
   * @inheritDoc
   */
  public function getFile(AbstractClearingItemEntity $clearingItem): ?ExternalFileEntity {
    if (NULL === $clearingItem->getFileId()) {
      return NULL;
    }

    return $this->externalFileManager->getFileByFileId(
      $clearingItem->getFileId(),
      FundingClearingProcess::getEntityName(),
      $clearingItem->getClearingProcessId()
    );
  }

  /**
   * @inheritDoc
   */
  public function getFiles(int $clearingProcessId): array {
    return $this->externalFileManager->getFiles(FundingClearingProcess::getEntityName(), $clearingProcessId);
  }

  private function getIdentifierPrefix(int $clearingProcessId): string {
    return FundingClearingProcess::getEntityName() . '.' . $clearingProcessId . ':';
  }

}
