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

use Civi\Funding\Util\ArrayUtil;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

/**
 * To be used together with JSON schemas using the tag 'externalFile'
 * ("$tag": 'externalFile') for files referenced by URI to store in CiviCRM.
 */
final class TaggedExternalFilePersister {

  private TaggedExternalFileManager $externalFileManager;

  public function __construct(TaggedExternalFileManager $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @param array<string, mixed> $data
   *   External URIs will be changed to Civi external file download URIs.
   *
   * @phpstan-return array<string, string>
   *   Mapping of input URI to Civi external file download URI.
   *
   * @throws \CRM_Core_Exception
   */
  public function handleFiles(
    TaggedDataContainerInterface $taggedData,
    array &$data,
    string $entityName,
    int $entityId
  ): array {
    /** @phpstan-var array<non-empty-string, non-empty-string> $uris */
    $uris = $taggedData->getByTag('externalFile');
    $externalFiles = $this->externalFileManager->updateAllFiles(
      $uris,
      $entityName,
      $entityId
    );

    $result = [];
    foreach ($externalFiles as $uri => $externalFile) {
      /** @var non-empty-string $dataPointer */
      // @phpstan-ignore-next-line
      $dataPointer = $externalFile->getCustomData()['dataPointer'];
      ArrayUtil::setValueAtPointer($data, $dataPointer, $externalFile->getUri());

      $result[$uri] = $externalFile->getUri();
    }

    return $result;
  }

}
