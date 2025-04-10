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

namespace Civi\Funding\Fixtures;

use Civi\Funding\Entity\FundingCaseBundle;

final class FundingCaseBundleFixture {

  /**
   * @phpstan-param array<string, mixed> $fundingCaseValues
   * @phpstan-param array<string, scalar> $fundingCaseTypeValues
   * @phpstan-param array<string, scalar|null> $fundingProgramValues
   *
   * @throws \CRM_Core_Exception
   */
  public static function create(
    array $fundingCaseValues = [],
    array $fundingCaseTypeValues = [],
    array $fundingProgramValues = []
  ): FundingCaseBundle {
    $fundingProgram = FundingProgramFixture::addFixture($fundingProgramValues);
    $fundingCaseType = FundingCaseTypeFixture::addFixture($fundingCaseTypeValues);
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      $fundingCaseValues
    );

    return new FundingCaseBundle(
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );
  }

}
