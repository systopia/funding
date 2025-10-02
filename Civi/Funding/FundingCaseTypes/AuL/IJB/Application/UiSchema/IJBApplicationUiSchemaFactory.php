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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;

final class IJBApplicationUiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    return new IJBApplicationUiSchema($applicationProcessBundle->getFundingProgram()->getCurrency());
  }

  /**
   * @inheritDoc
   */
  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    return new IJBApplicationUiSchema($fundingProgram->getCurrency());
  }

  public function createUiSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonFormsLayout {
    return $this->createUiSchemaNew($fundingProgram, $fundingCaseType);
  }

}
