<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;

/**
 * @phpstan-type dateienT array<array{
 *   _identifier?: string,
 *   datei: string,
 *   beschreibung: string,
 * }>
 */
class HiHInfoDateienFactory {

  private ApplicationExternalFileManagerInterface $externalFileManager;

  public function __construct(ApplicationExternalFileManagerInterface $externalFileManager) {
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @phpstan-return dateienT
   *
   * @throws \CRM_Core_Exception
   */
  public function createInfoDateien(ApplicationProcessEntity $applicationProcess): array {
    $dateien = [];
    foreach ($this->externalFileManager->getFiles($applicationProcess->getId()) as $identifier => $file) {
      if (str_starts_with($identifier, 'infoDatei/')) {
        /** @var string $beschreibung */
        $beschreibung = $file->getCustomData()['beschreibung'] ?? '';
        $dateien[] = [
          '_identifier' => $identifier,
          'datei' => $file->getUri(),
          'beschreibung' => $beschreibung,
        ];
      }
    }

    return $dateien;
  }

}
