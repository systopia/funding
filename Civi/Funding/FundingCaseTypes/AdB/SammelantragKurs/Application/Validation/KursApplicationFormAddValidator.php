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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Application\Validation;

use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormAddValidatorInterface;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursApplicationFormAddValidator implements ApplicationFormAddValidatorInterface {

  use KursSupportedFundingCaseTypesTrait;

  public function validateAdd(
    FundingCaseBundle $fundingCaseBundle,
    ApplicationSchemaValidationResult $schemaValidationResult,
    bool $readOnly
  ): ApplicationFormValidationResult {
    // @phpstan-ignore if.condNotBoolean
    if ($schemaValidationResult->getData()['nichtGefoerdert']) {
      $costItemsData = [];
      $resourcesItemsData = [];
    }
    else {
      $costItemsData = $schemaValidationResult->getCostItemsData();
      $resourcesItemsData = $schemaValidationResult->getResourcesItemsData();
    }

    return new ApplicationFormValidationResult(
      $schemaValidationResult->getLeafErrorMessages(),
      $schemaValidationResult->getData(),
      $costItemsData,
      $resourcesItemsData,
      $schemaValidationResult->getTaggedData(),
      $readOnly
    );
  }

}
