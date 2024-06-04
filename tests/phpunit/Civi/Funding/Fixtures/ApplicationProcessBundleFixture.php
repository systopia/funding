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

use Civi\Funding\Entity\ApplicationProcessEntityBundle;

final class ApplicationProcessBundleFixture {

  /**
   * @phpstan-param array<string, mixed> $applicationProcessValues
   * @phpstan-param array<string, scalar> $fundingCaseTypeValues
   *
   * @throws \CRM_Core_Exception
   */
  public static function create(
    array $applicationProcessValues = [],
    array $fundingCaseTypeValues = []
  ): ApplicationProcessEntityBundle {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture($fundingCaseTypeValues);
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), $applicationProcessValues);

    return new ApplicationProcessEntityBundle(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );
  }

}
