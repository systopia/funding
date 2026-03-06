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

use Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface;
use Civi\Funding\Form\FundingFormFile;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;
use Civi\Funding\Util\Uuid;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type dokumenteT list<array{
 *   _identifier?: string,
 *   datei: string,
 *   beschreibung: string,
 * }>
 */
final class PersonalkostenApplicationFormFilesFactory implements ApplicationFormFilesFactoryInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    /** @phpstan-var dokumenteT $dokumente */
    $dokumente = &$requestData['dokumente'];
    foreach ($dokumente as &$dokument) {
      if ('' === ($dokument['_identifier'] ?? '')) {
        $dokument['_identifier'] = 'dokument/' . Uuid::generateRandom();
      }
    }

    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function createFormFiles(array $requestData): array {
    $files = [];
    /** @phpstan-var dokumenteT $dokumente */
    $dokumente = $requestData['dokumente'];
    foreach ($dokumente as $dokument) {
      Assert::keyExists($dokument, '_identifier');
      $files[] = new FundingFormFile(
        $dokument['datei'],
        $dokument['_identifier'],
        ['beschreibung' => $dokument['beschreibung']],
      );
    }

    return $files;
  }

}
