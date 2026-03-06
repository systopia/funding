<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;

/**
 * @phpstan-import-type dokumenteT from PersonalkostenApplicationFormFilesFactory
 */
final class PersonalkostenDokumenteFactory {

  private ApplicationExternalFileManagerInterface $externalFileManager;

  public function __construct(ApplicationExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @phpstan-return dokumenteT
   *
   * @throws \CRM_Core_Exception
   */
  public function createDokumente(ApplicationProcessEntity $applicationProcess): array {
    $dokumente = [];
    foreach ($this->externalFileManager->getFiles($applicationProcess->getId()) as $identifier => $file) {
      if (str_starts_with($identifier, 'dokument/')) {
        /** @var string $beschreibung */
        $beschreibung = $file->getCustomData()['beschreibung'] ?? '';
        $dokumente[] = [
          '_identifier' => $identifier,
          'datei' => $file->getUri(),
          'beschreibung' => $beschreibung,
        ];
      }
    }

    return $dokumente;
  }

}
