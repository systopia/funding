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

namespace Civi\Funding\ApplicationProcess\Form\Validation;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Psr\Container\ContainerInterface;

/**
 * @codeCoverageIgnore
 */
final class ApplicationFormNewValidatorCollector implements ApplicationFormNewValidatorInterface {

  private ContainerInterface $validators;

  /**
   * @param \Psr\Container\ContainerInterface $validators
   *   Validators with funding case type name as ID.
   */
  public function __construct(ContainerInterface $validators) {
    $this->validators = $validators;
  }

  public function validateInitial(
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram,
    ApplicationSchemaValidationResult $schemaValidationResult,
    bool $readOnly
  ): ApplicationFormValidationResult {
    if ($this->validators->has($fundingCaseType->getName())) {
      /** @var \Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormNewValidatorInterface $validator */
      $validator = $this->validators->get($fundingCaseType->getName());

      return $validator->validateInitial($fundingCaseType, $fundingProgram, $schemaValidationResult, $readOnly);
    }

    return new ApplicationFormValidationResult(
      $schemaValidationResult->getLeafErrorMessages(),
      $schemaValidationResult->getData(),
      $schemaValidationResult->getCostItemsData(),
      $schemaValidationResult->getResourcesItemsData(),
      $schemaValidationResult->getTaggedData(),
      $readOnly
    );
  }

}
