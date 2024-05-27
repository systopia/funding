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

namespace Civi\Funding\ExternalFile;

use Civi\Funding\Util\Uuid;
use Civi\RemoteTools\Api4\Query\Comparison;

/**
 * To be used with JSON schemas where URIs are tagged with 'externalFile'.
 *
 * @see \Civi\Funding\ExternalFile\TaggedExternalFilePersister
 */
class TaggedExternalFileManager {

  private const IDENTIFIER_PREFIX = 'tagged:';

  private FundingExternalFileManagerInterface $externalFileManager;

  public function __construct(FundingExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @phpstan-return array<non-empty-string, \Civi\Funding\Entity\ExternalFileEntity>
   *   Mapping of URI to file.
   *
   * @throws \CRM_Core_Exception
   */
  public function getFiles(string $entityName, int $entityId): array {
    $files = [];
    foreach ($this->externalFileManager->getFiles(
      $entityName,
      $entityId,
      Comparison::new('identifier', 'LIKE', self::IDENTIFIER_PREFIX . '%')
    ) as $file) {
      $files[$file->getUri()] = $file;
    }

    return $files;
  }

  /**
   * @phpstan-param array<non-empty-string, non-empty-string> $uris
   *   Mapping of data pointers to URIs.
   *
   * @phpstan-return array<non-empty-string, \Civi\Funding\Entity\ExternalFileEntity>
   *   Mapping of given URIs to files. The data pointer is part of the custom
   *   data at key 'dataPointer'.
   *
   * @throws \CRM_Core_Exception
   */
  public function updateAllFiles(array $uris, string $entityName, int $entityId): array {
    $existingFiles = $this->getFiles($entityName, $entityId);

    $newFiles = [];
    foreach ($uris as $dataPointer => $uri) {
      $customData = ['dataPointer' => $dataPointer];
      if (isset($existingFiles[$uri])) {
        $this->externalFileManager->updateCustomData($existingFiles[$uri], $customData);
        $newFiles[$uri] = $existingFiles[$uri];
      }
      else {
        $newFiles[$uri] = $this->externalFileManager->addFile(
          $uri,
          $this->generateIdentifier(),
          $entityName,
          $entityId,
          $customData
        );
      }
    }

    $deletedFiles = array_diff_key($existingFiles, $newFiles);
    foreach ($deletedFiles as $file) {
      $this->externalFileManager->deleteFile($file);
    }

    return $newFiles;
  }

  private function generateIdentifier(): string {
    return self::IDENTIFIER_PREFIX . Uuid::generateRandom();
  }

}
