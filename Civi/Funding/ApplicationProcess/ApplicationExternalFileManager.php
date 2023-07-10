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
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\FundingExternalFileManagerInterface;

final class ApplicationExternalFileManager implements ApplicationExternalFileManagerInterface {

  private const TABLE = 'civicrm_application_process';

  private FundingExternalFileManagerInterface $externalFileManager;

  public function __construct(FundingExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  public function addOrUpdateFile(
    string $uri,
    string $identifier,
    int $applicationProcessId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    return $this->externalFileManager->addOrUpdateFile(
      $uri,
      $identifier,
      self::TABLE,
      $applicationProcessId,
      $this->buildCustomData($applicationProcessId, $customData),
    );
  }

  public function deleteFile(ExternalFileEntity $externalFile): void {
    $this->externalFileManager->deleteFile($externalFile);
  }

  public function deleteFiles(int $applicationProcessId, array $excludedIdentifiers): void {
    $this->externalFileManager->deleteFiles(self::TABLE, $applicationProcessId, $excludedIdentifiers);
  }

  public function getFile(string $identifier, int $applicationProcessId): ?ExternalFileEntity {
    return $this->externalFileManager->getFile($identifier, self::TABLE, $applicationProcessId);
  }

  public function getFiles(int $applicationProcessId): array {
    return $this->externalFileManager->getFiles(self::TABLE, $applicationProcessId);
  }

  /**
   * @phpstan-param array<int|string, mixed>|null $customData
   *
   * @phpstan-return array<int|string, mixed>
   */
  private function buildCustomData(int $applicationProcessId, ?array $customData): array {
    return [
      'entityName' => FundingApplicationProcess::_getEntityName(),
      'entityId' => $applicationProcessId,
    ] + ($customData ?? []);
  }

}
