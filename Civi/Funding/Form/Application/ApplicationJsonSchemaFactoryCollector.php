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
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Psr\Container\ContainerInterface;

final class ApplicationJsonSchemaFactoryCollector implements ApplicationJsonSchemaFactoryInterface {

  private ContainerInterface $factories;

  /**
   * @inheritDoc
   */
  public static function getSupportedFundingCaseTypes(): array {
    return [];
  }

  /**
   * @param \Psr\Container\ContainerInterface $factories
   *   JSON schema factories with funding case type name as ID.
   */
  public function __construct(ContainerInterface $factories) {
    $this->factories = $factories;
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema {
    return $this->getFactory($applicationProcessBundle->getFundingCaseType())
      ->createJsonSchemaExisting($applicationProcessBundle, $applicationProcessStatusList);
  }

  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema {
    return $this->getFactory($fundingCaseType)->createJsonSchemaForTranslation($fundingProgram, $fundingCaseType);
  }

  private function getFactory(FundingCaseTypeEntity $fundingCaseType): ApplicationJsonSchemaFactoryInterface {
    // @phpstan-ignore return.type
    return $this->factories->get($fundingCaseType->getName());
  }

}
