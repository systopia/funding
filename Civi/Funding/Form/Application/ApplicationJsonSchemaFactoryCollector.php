<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\Application;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;
use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<ApplicationJsonSchemaFactoryInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class ApplicationJsonSchemaFactoryCollector extends AbstractFundingCaseTypeServiceCollector implements ApplicationJsonSchemaFactoryInterface {

  /**
   * @inheritDoc
   */
  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema {
    return $this
      ->getService($applicationProcessBundle->getFundingCaseType()->getName())
      ->createJsonSchemaExisting($applicationProcessBundle, $applicationProcessStatusList);
  }

  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema {
    return $this
      ->getService($fundingCaseType->getName())
      ->createJsonSchemaForTranslation($fundingProgram, $fundingCaseType);
  }

}
