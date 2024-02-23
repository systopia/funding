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

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\FundingExternalFileManagerInterface;

final class ClearingExternalFileManager implements ClearingExternalFileManagerInterface {

  private const TABLE = 'civicrm_funding_clearing_process';

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
    int $clearingProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $identifier = $this->addIdentifierPrefix($clearingProcessId, $identifier);
    $externalFile = $this->getFile($identifier, $clearingProcessId);
    if (NULL !== $externalFile
      && $this->externalFileManager->isFileChanged($externalFile, $uri)) {
      $this->externalFileManager->deleteFile($externalFile);

      return $this->externalFileManager->addFile(
        $uri,
        $identifier,
        self::TABLE,
        $clearingProcessId,
        $this->buildCustomData($clearingProcessId, $customData)
      );
    }

    return $this->externalFileManager->addOrUpdateFile(
      $uri,
      $identifier,
      self::TABLE,
      $clearingProcessId,
      $this->buildCustomData($clearingProcessId, $customData),
    );
  }

  /**
   * @inheritDoc
   */
  public function getFile(string $identifier, int $clearingProcessId): ?ExternalFileEntity {
    $identifier = $this->addIdentifierPrefix($clearingProcessId, $identifier);

    return $this->externalFileManager->getFile($identifier, self::TABLE, $clearingProcessId);
  }

  /**
   * @inheritDoc
   */
  public function getFiles(int $clearingProcessId): array {
    return $this->buildExternalFilesMap(
      $this->externalFileManager->getFiles(self::TABLE, $clearingProcessId),
      $clearingProcessId,
    );
  }

  private function addIdentifierPrefix(int $clearingProcessId, string $identifier): string {
    $prefix = $this->getIdentifierPrefix($clearingProcessId);

    return str_starts_with($identifier, $prefix) ? $identifier : ($prefix . $identifier);
  }

  /**
   * @phpstan-param array<int|string, mixed>|null $customData
   *
   * @phpstan-return array<int|string, mixed>
   */
  private function buildCustomData(int $clearingProcessId, ?array $customData): array {
    return [
      'entityName' => FundingClearingProcess::getEntityName(),
      'entityId' => $clearingProcessId,
    ] + ($customData ?? []);
  }

  /**
   * @phpstan-param array<ExternalFileEntity> $externalFiles
   *
   * @phpstan-return array<string, ExternalFileEntity>
   *   Key is the identifier without prefix.
   *
   * @see addIdentifierPrefix()
   */
  private function buildExternalFilesMap(array $externalFiles, int $clearingProcessId): array {
    $externalFilesMap = [];
    foreach ($externalFiles as $externalFile) {
      $prefixlessIdentifier = $this->stripIdentifierPrefix($externalFile->getIdentifier(), $clearingProcessId);
      $externalFilesMap[$prefixlessIdentifier] = $externalFile;
    }

    return $externalFilesMap;
  }

  private function getIdentifierPrefix(int $clearingProcessId): string {
    return FundingClearingProcess::getEntityName() . '.' . $clearingProcessId . ':';
  }

  private function stripIdentifierPrefix(string $identifier, int $clearingProcessId): string {
    $prefix = $this->getIdentifierPrefix($clearingProcessId);

    // @phpstan-ignore-next-line
    return preg_replace('/^' . $prefix . '/', '', $identifier);
  }

}
