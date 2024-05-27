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

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\ExternalFile\FundingExternalFileManagerInterface;

final class ApplicationExternalFileManager implements ApplicationExternalFileManagerInterface {

  private FundingExternalFileManagerInterface $externalFileManager;

  public function __construct(FundingExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @inheritDoc
   */
  public function addOrUpdateFile(
    string $uri,
    string $identifier,
    int $applicationProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $identifier = $this->addIdentifierPrefix($applicationProcessId, $identifier);
    $externalFile = $this->getFile($identifier, $applicationProcessId);
    if (NULL !== $externalFile
      && $this->externalFileManager->isFileChanged($externalFile, $uri)) {
      $this->deleteFile($externalFile, $applicationProcessId);

      return $this->externalFileManager->addFile(
        $uri,
        $identifier,
        FundingApplicationProcess::getEntityName(),
        $applicationProcessId,
        $customData
      );
    }

    return $this->externalFileManager->addOrUpdateFile(
      $uri,
      $identifier,
      FundingApplicationProcess::getEntityName(),
      $applicationProcessId,
      $customData,
    );
  }

  /**
   * @inheritDoc
   */
  public function attachFileToSnapshot(ExternalFileEntity $externalFile, int $snapshotId): void {
    $this->externalFileManager->attachFile($externalFile, FundingApplicationSnapshot::getEntityName(), $snapshotId);
  }

  /**
   * @inheritDoc
   */
  public function deleteFiles(int $applicationProcessId, array $excludedIdentifiers): void {
    $excludedIdentifiers = array_map(
      fn (string $identifier) => $this->addIdentifierPrefix($applicationProcessId, $identifier),
      $excludedIdentifiers,
    );

    foreach ($this->getFiles($applicationProcessId) as $externalFile) {
      if (!in_array($externalFile->getIdentifier(), $excludedIdentifiers, TRUE)) {
        $this->deleteFile($externalFile, $applicationProcessId);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function getFile(string $identifier, int $applicationProcessId): ?ExternalFileEntity {
    $identifier = $this->addIdentifierPrefix($applicationProcessId, $identifier);

    return $this->externalFileManager->getFile(
      $identifier,
      FundingApplicationProcess::getEntityName(),
      $applicationProcessId
    );
  }

  /**
   * @inheritDoc
   */
  public function getFiles(int $applicationProcessId): array {
    return $this->buildExternalFilesMap(
      $this->externalFileManager->getFiles(FundingApplicationProcess::getEntityName(), $applicationProcessId),
      $applicationProcessId,
    );
  }

  /**
   * @inheritDoc
   */
  public function getFilesAttachedToSnapshot(int $snapshotId): array {
    return $this->externalFileManager->getFiles(FundingApplicationSnapshot::getEntityName(), $snapshotId);
  }

  /**
   * @inheritDoc
   */
  public function restoreFileSnapshot(ExternalFileEntity $externalFile, int $applicationProcessId): void {
    /** @var string $identifier */
    $identifier = preg_replace('/^snapshot@[0-9]+:/', '', $externalFile->getIdentifier());
    if ($externalFile->getIdentifier() !== $identifier) {
      $currentExternalFile = $this->getFile($identifier, $applicationProcessId);
      if (NULL !== $currentExternalFile) {
        $this->externalFileManager->deleteFile($currentExternalFile);
      }
      $this->externalFileManager->updateIdentifier($externalFile, $identifier);
    }

    $this->attachFile($externalFile, $applicationProcessId);
  }

  private function addIdentifierPrefix(int $applicationProcessId, string $identifier): string {
    $prefix = $this->getIdentifierPrefix($applicationProcessId);

    return str_starts_with($identifier, $prefix) ? $identifier : ($prefix . $identifier);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function attachFile(ExternalFileEntity $externalFile, int $applicationProcessId): void {
    $this->externalFileManager->attachFile(
      $externalFile,
      FundingApplicationProcess::getEntityName(),
      $applicationProcessId
    );
  }

  /**
   * @phpstan-param array<ExternalFileEntity> $externalFiles
   *
   * @phpstan-return array<string, ExternalFileEntity>
   *   Key is the identifier without prefix.
   *
   * @see addIdentifierPrefix()
   */
  private function buildExternalFilesMap(array $externalFiles, int $applicationProcessId): array {
    $externalFilesMap = [];
    foreach ($externalFiles as $externalFile) {
      $prefixlessIdentifier = $this->stripIdentifierPrefix($externalFile->getIdentifier(), $applicationProcessId);
      $externalFilesMap[$prefixlessIdentifier] = $externalFile;
    }

    return $externalFilesMap;
  }

  private function deleteFile(ExternalFileEntity $externalFile, int $applicationProcessId): void {
    if ($this->isUsedInSnapshot($externalFile)) {
      $this->makeSnapshot($externalFile, $applicationProcessId);
    }
    else {
      $this->externalFileManager->deleteFile($externalFile);
    }
  }

  private function getIdentifierPrefix(int $applicationProcessId): string {
    return FundingApplicationProcess::getEntityName() . '.' . $applicationProcessId . ':';
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function isUsedInSnapshot(ExternalFileEntity $externalFile): bool {
    return $this->externalFileManager->isAttachedToEntityType(
      $externalFile,
      FundingApplicationSnapshot::getEntityName()
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function makeSnapshot(ExternalFileEntity $externalFile, int $applicationProcessId): void {
    $identifier = 'snapshot@' . time() . ':' . $externalFile->getIdentifier();
    $this->externalFileManager->updateIdentifier($externalFile, $identifier);
    $this->externalFileManager->detachFile(
      $externalFile,
      FundingApplicationProcess::getEntityName(),
      $applicationProcessId
    );
  }

  private function stripIdentifierPrefix(string $identifier, int $applicationProcessId): string {
    $prefix = $this->getIdentifierPrefix($applicationProcessId);

    // @phpstan-ignore-next-line
    return preg_replace('/^' . $prefix . '/', '', $identifier);
  }

}
