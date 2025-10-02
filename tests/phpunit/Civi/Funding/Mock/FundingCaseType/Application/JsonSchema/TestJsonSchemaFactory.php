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

namespace Civi\Funding\Mock\FundingCaseType\Application\JsonSchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\CombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Mock\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

// phpcs:disable Generic.Files.LineLength.TooLong
class TestJsonSchemaFactory implements CombinedApplicationJsonSchemaFactoryInterface, NonCombinedApplicationJsonSchemaFactoryInterface {
// phpcs:enable

  use TestSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function createJsonSchemaAdd(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ): JsonSchema {
    return new TestJsonSchema(FALSE);
  }

  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema {
    return new TestJsonSchema(FALSE);
  }

  public function createJsonSchemaInitial(
    int $contactId,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    return new TestJsonSchema(TRUE);
  }

  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema {
    return $this->createJsonSchemaInitial(0, $fundingCaseType, $fundingProgram);
  }

}
