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

namespace Civi\Funding\SonstigeAktivitaet;

use Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface;
use Civi\Funding\Form\FundingFormFile;
use Civi\Funding\Util\Uuid;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type projektunterlagenT array<array{
 *   _identifier?: string,
 *   datei: string,
 *   beschreibung: string,
 * }>
 */
final class AVK1ApplicationFormFilesFactory implements ApplicationFormFilesFactoryInterface {

  /**
   * @inheritDoc
   */
  public static function getSupportedFundingCaseTypes(): array {
    return ['AVK1SonstigeAktivitaet'];
  }

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    /** @phpstan-var projektunterlagenT $projektunterlagen */
    $projektunterlagen = &$requestData['projektunterlagen'];
    foreach ($projektunterlagen as &$projektunterlage) {
      if ('' === ($projektunterlage['_identifier'] ?? '')) {
        $projektunterlage['_identifier'] = 'projektunterlage/' . Uuid::generateRandom();
      }
    }

    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function createFormFiles(array $requestData): array {
    $files = [];
    /** @phpstan-var projektunterlagenT $projektunterlagen */
    $projektunterlagen = $requestData['projektunterlagen'];
    foreach ($projektunterlagen as $projektunterlage) {
      Assert::keyExists($projektunterlage, '_identifier');
      $files[] = new FundingFormFile(
        $projektunterlage['datei'],
        $projektunterlage['_identifier'],
        ['beschreibung' => $projektunterlage['beschreibung']],
      );
    }

    return $files;
  }

}
