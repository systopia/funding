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

use Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface;
use Civi\Funding\Form\FundingFormFile;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\Funding\Util\Uuid;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type dateienT array<array{
 *   _identifier?: string,
 *   datei: string,
 *   beschreibung: string,
 * }>
 */
final class HiHApplicationFormFilesFactory implements ApplicationFormFilesFactoryInterface {

  use HiHSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    /** @phpstan-var dateienT $dateien */
    // @phpstan-ignore offsetAccess.nonOffsetAccessible
    $dateien = &$requestData['informationenZumProjekt']['dateien'];
    foreach ($dateien as &$datei) {
      if ('' === ($datei['_identifier'] ?? '')) {
        $datei['_identifier'] = 'infoDatei/' . Uuid::generateRandom();
      }
    }

    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function createFormFiles(array $requestData): array {
    $files = [];
    /** @phpstan-var dateienT $dateien */
    // @phpstan-ignore offsetAccess.nonOffsetAccessible
    $dateien = $requestData['informationenZumProjekt']['dateien'];
    foreach ($dateien as $datei) {
      Assert::keyExists($datei, '_identifier');
      $files[] = new FundingFormFile(
        $datei['datei'],
        $datei['_identifier'],
        ['beschreibung' => $datei['beschreibung']],
      );
    }

    return $files;
  }

}
