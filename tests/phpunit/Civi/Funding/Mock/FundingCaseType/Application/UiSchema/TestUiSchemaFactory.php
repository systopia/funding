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

namespace Civi\Funding\Mock\FundingCaseType\Application\UiSchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\CombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\Mock\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;

// phpcs:disable Generic.Files.LineLength.TooLong
final class TestUiSchemaFactory implements CombinedApplicationUiSchemaFactoryInterface, NonCombinedApplicationUiSchemaFactoryInterface {
// phpcs:enable
  use TestSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function createUiSchemaAdd(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ): JsonFormsLayout {
    return new TestUiSchema();
  }

  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    return new TestUiSchema();
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    return new TestUiSchema();
  }

}
