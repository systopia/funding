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

use Civi\Api4\_EntityFile;
use Civi\Api4\ExternalFile;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use CRM_Funding_ExtensionUtil as E;

final class FundingExternalFileManager implements FundingExternalFileManagerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function addFile(
    string $uri,
    string $identifier,
    string $entityTable,
    int $entityId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $result = $this->api4->createEntity('ExternalFile', [
      'identifier' => $identifier,
      'source' => $uri,
      'custom_data' => $customData,
      'extension' => E::SHORT_NAME,
    ], ['checkPermissions' => FALSE]);

    $externalFile = ExternalFileEntity::singleFromApiResult($result);
    $this->attachFile($externalFile, $entityTable, $entityId);

    return $externalFile;
  }

  public function addOrUpdateFile(
    string $uri,
    string $identifier,
    string $entityTable,
    int $entityId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $externalFile = $this->getFile($identifier, $entityTable, $entityId);
    if (NULL !== $externalFile) {
      if ($externalFile->getUri() !== $uri) {
        $this->deleteFile($externalFile);
      }
      else {
        $this->updateCustomData($externalFile, $customData);

        return $externalFile;
      }
    }

    return $this->addFile($uri, $identifier, $entityTable, $entityId, $customData);
  }

  public function attachFile(ExternalFileEntity $externalFile, string $entityTable, int $entityId): void {
    $this->attachCiviFile($externalFile->getFileId(), $entityTable, $entityId);
  }

  public function deleteFile(ExternalFileEntity $externalFile): void {
    $this->api4->deleteEntity('ExternalFile', $externalFile->getId(), ['checkPermissions' => FALSE]);
  }

  public function deleteFiles(string $entityTable, int $entityId, array $excludedIdentifiers): void {
    foreach ($this->getFiles($entityTable, $entityId) as $externalFile) {
      if (!in_array($externalFile->getIdentifier(), $excludedIdentifiers, TRUE)) {
        $this->deleteFile($externalFile);
      }
    }
  }

  public function getFile(string $identifier, string $entityTable, int $entityId): ?ExternalFileEntity {
    $action = ExternalFile::get(FALSE)
      ->addWhere('extension', '=', E::SHORT_NAME)
      ->addWhere('identifier', '=', $identifier);
    $externalFile = ExternalFileEntity::singleOrNullFromApiResult($this->api4->executeAction($action));

    if (NULL === $externalFile) {
      return NULL;
    }

    $countAction = _EntityFile::get(FALSE)
      ->selectRowCount()
      ->addWhere('file_id', '=', $externalFile->getFileId())
      ->addWhere('entity_table', '=', $entityTable)
      ->addWhere('entity_id', '=', $entityId);
    if (0 === $this->api4->executeAction($countAction)->count()) {
      return NULL;
    }

    return $externalFile;
  }

  public function getFiles(string $entityTable, int $entityId): array {
    $fileIds = $this->getFileIdsByEntity($entityTable, $entityId);
    if ([] === $fileIds) {
      return [];
    }

    $result = $this->api4->getEntities(
      'ExternalFile',
      CompositeCondition::fromFieldValuePairs([
        'file_id' => $fileIds,
        'extension' => E::SHORT_NAME,
      ]),
      [],
      0,
      0,
      ['checkPermissions' => FALSE]
    );

    return ExternalFileEntity::allFromApiResult($result);
  }

  public function updateCustomData(ExternalFileEntity $externalFile, ?array $customData): void {
    if ($externalFile->getCustomData() != $customData) {
      $externalFile->setCustomData($customData);
      $this->api4->updateEntity(
        'ExternalFile',
        $externalFile->getId(),
        ['custom_data' => $customData],
        ['checkPermissions' => FALSE],
      );
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function attachCiviFile(int $fileId, string $entityTable, int $entityId): void {
    $countAction = _EntityFile::get(FALSE)
      ->selectRowCount()
      ->addWhere('file_id', '=', $fileId)
      ->addWhere('entity_table', '=', $entityTable)
      ->addWhere('entity_id', '=', $entityId);

    if (0 === $this->api4->executeAction($countAction)->count()) {
      $entityFileAction = _EntityFile::create(FALSE)
        ->setValues([
          'file_id' => $fileId,
          'entity_table' => $entityTable,
          'entity_id' => $entityId,
        ]);
      $this->api4->executeAction($entityFileAction);
    }
  }

  /**
   * @phpstan-return array<int>
   *
   * @throws \CRM_Core_Exception
   */
  private function getFileIdsByEntity(string $entityTable, int $entityId): array {
    $entityFileAction = _EntityFile::get(FALSE)
      ->addSelect('file_id')
      ->addWhere('entity_table', '=', $entityTable)
      ->addWhere('entity_id', '=', $entityId);

    return $this->api4->executeAction($entityFileAction)->column('file_id');
  }

}
