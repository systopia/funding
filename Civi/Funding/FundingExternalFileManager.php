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

use Civi\Api4\EntityFile;
use Civi\Api4\ExternalFile;
use Civi\Funding\Database\DaoEntityInfoProvider;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use CRM_Funding_ExtensionUtil as E;

final class FundingExternalFileManager implements FundingExternalFileManagerInterface {

  private Api4Interface $api4;

  private DaoEntityInfoProvider $daoEntityInfoProvider;

  public function __construct(Api4Interface $api4, DaoEntityInfoProvider $daoEntityInfoProvider) {
    $this->api4 = $api4;
    $this->daoEntityInfoProvider = $daoEntityInfoProvider;
  }

  /**
   * @inheritDoc
   */
  public function addFile(
    string $uri,
    string $identifier,
    string $entityName,
    int $entityId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $result = $this->api4->createEntity('ExternalFile', [
      'identifier' => $identifier,
      'source' => $uri,
      'custom_data' => $this->buildCustomData($entityName, $entityId, $customData),
      'extension' => E::SHORT_NAME,
    ]);

    $externalFile = ExternalFileEntity::singleFromApiResult($result);
    $this->attachFile($externalFile, $entityName, $entityId);

    return $externalFile;
  }

  /**
   * @inheritDoc
   */
  public function addOrUpdateFile(
    string $uri,
    string $identifier,
    string $entityName,
    int $entityId,
    ?array $customData = NULL
  ): ExternalFileEntity {
    $externalFile = $this->getFile($identifier, $entityName, $entityId);
    if (NULL !== $externalFile) {
      if ($this->isFileChanged($externalFile, $uri)) {
        $this->deleteFile($externalFile);
      }
      else {
        $this->updateCustomData($externalFile, $this->buildCustomData($entityName, $entityId, $customData));

        return $externalFile;
      }
    }

    return $this->addFile($uri, $identifier, $entityName, $entityId, $customData);
  }

  /**
   * @inheritDoc
   */
  public function attachFile(ExternalFileEntity $externalFile, string $entityName, int $entityId): void {
    $this->attachCiviFile($externalFile->getFileId(), $entityName, $entityId);
  }

  /**
   * @inheritDoc
   */
  public function deleteFile(ExternalFileEntity $externalFile): void {
    $this->api4->deleteEntity('ExternalFile', $externalFile->getId());
  }

  /**
   * @inheritDoc
   */
  public function deleteFiles(string $entityName, int $entityId, array $excludedIdentifiers): void {
    foreach ($this->getFiles($entityName, $entityId) as $externalFile) {
      if (!in_array($externalFile->getIdentifier(), $excludedIdentifiers, TRUE)) {
        $this->deleteFile($externalFile);
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function detachFile(ExternalFileEntity $externalFile, string $entityName, int $entityId): void {
    $entityFileAction = EntityFile::delete(FALSE)
      ->addWhere('file_id', '=', $externalFile->getFileId())
      ->addWhere('entity_table', '=', $this->daoEntityInfoProvider->getTable($entityName))
      ->addWhere('entity_id', '=', $entityId);
    $this->api4->executeAction($entityFileAction);
  }

  /**
   * @inheritDoc
   */
  public function getFile(string $identifier, string $entityName, int $entityId): ?ExternalFileEntity {
    $action = ExternalFile::get(FALSE)
      ->addWhere('extension', '=', E::SHORT_NAME)
      ->addWhere('identifier', '=', $identifier);
    $externalFile = ExternalFileEntity::singleOrNullFromApiResult($this->api4->executeAction($action));

    if (NULL === $externalFile) {
      return NULL;
    }

    $countAction = EntityFile::get(FALSE)
      ->selectRowCount()
      ->addWhere('file_id', '=', $externalFile->getFileId())
      ->addWhere('entity_table', '=', $this->daoEntityInfoProvider->getTable($entityName))
      ->addWhere('entity_id', '=', $entityId);
    if (0 === $this->api4->executeAction($countAction)->count()) {
      return NULL;
    }

    return $externalFile;
  }

  /**
   * @inheritDoc
   */
  public function getFiles(string $entityName, int $entityId, ?ConditionInterface $condition = NULL): array {
    $fileIds = $this->getFileIdsByEntity($entityName, $entityId);
    if ([] === $fileIds) {
      return [];
    }

    $conditions = [
      Comparison::new('file_id', 'IN', $fileIds),
      Comparison::new('extension', '=', E::SHORT_NAME),
    ];
    if (NULL !== $condition) {
      $conditions[] = $condition;
    }

    $result = $this->api4->getEntities(
      'ExternalFile',
      CompositeCondition::new('AND', ...$conditions),
      ['id' => 'ASC']
    );

    return ExternalFileEntity::allFromApiResult($result);
  }

  /**
   * @inheritDoc
   */
  public function isAttachedToEntityType(ExternalFileEntity $externalFile, string $entityName): bool {
    $action = EntityFile::get(FALSE)
      ->selectRowCount()
      ->addWhere('file_id', '=', $externalFile->getFileId())
      ->addWhere('entity_table', '=', $this->daoEntityInfoProvider->getTable($entityName));

    return 0 < $this->api4->executeAction($action)->countMatched();
  }

  /**
   * @inheritDoc
   */
  public function isFileChanged(ExternalFileEntity $externalFile, string $newUri): bool {
    return $externalFile->getUri() !== $newUri;
  }

  /**
   * @inheritDoc
   */
  public function updateCustomData(ExternalFileEntity $externalFile, array $customData): void {
    $customData['entityName'] ??= $externalFile->getCustomData()['entityName'] ?? NULL;
    $customData['entityId'] ??= $externalFile->getCustomData()['entityId'] ?? NULL;
    if ($externalFile->getCustomData() != $customData) {
      $externalFile->setCustomData($customData);
      $this->api4->updateEntity(
        'ExternalFile',
        $externalFile->getId(),
        ['custom_data' => $customData],
      );
    }
  }

  /**
   * @inheritDoc
   */
  public function updateIdentifier(ExternalFileEntity $externalFile, string $identifier): void {
    if ($externalFile->getIdentifier() !== $identifier) {
      $externalFile->setIdentifier($identifier);
      $this->api4->updateEntity(
        'ExternalFile',
        $externalFile->getId(),
        ['identifier' => $identifier]
      );
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function attachCiviFile(int $fileId, string $entityName, int $entityId): void {
    $countAction = EntityFile::get(FALSE)
      ->selectRowCount()
      ->addWhere('file_id', '=', $fileId)
      ->addWhere('entity_table', '=', $this->daoEntityInfoProvider->getTable($entityName))
      ->addWhere('entity_id', '=', $entityId);

    if (0 === $this->api4->executeAction($countAction)->count()) {
      $entityFileAction = EntityFile::create(FALSE)
        ->setValues([
          'file_id' => $fileId,
          'entity_table' => $this->daoEntityInfoProvider->getTable($entityName),
          'entity_id' => $entityId,
        ]);
      $this->api4->executeAction($entityFileAction);
    }
  }

  /**
   * @phpstan-param array<int|string, mixed>|null $customData
   *
   * @phpstan-return array<int|string, mixed>
   */
  private function buildCustomData(string $entityName, int $entityId, ?array $customData): array {
    return [
      'entityName' => $entityName,
      'entityId' => $entityId,
    ] + ($customData ?? []);
  }

  /**
   * @phpstan-return list<int>
   *
   * @throws \CRM_Core_Exception
   */
  private function getFileIdsByEntity(string $entityName, int $entityId): array {
    $entityFileAction = EntityFile::get(FALSE)
      ->addSelect('file_id')
      ->addWhere('entity_table', '=', $this->daoEntityInfoProvider->getTable($entityName))
      ->addWhere('entity_id', '=', $entityId);

    return $this->api4->executeAction($entityFileAction)->column('file_id');
  }

}
