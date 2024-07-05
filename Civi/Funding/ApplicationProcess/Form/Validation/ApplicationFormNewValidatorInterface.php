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

/**
 * Used when validating a new application that usually will lead to a new
 * funding case, i.e. for non-combined applications.
 *
 * This can be used if additional validation to JSON schema validation is
 * required or if additional mapped values shall be set.
 *
 * Implementations should be registered as service tagged with SERVICE_TAG and
 * add the public static method getFundingCaseType() or getFundingCaseTypes().
 */
interface ApplicationFormNewValidatorInterface {

  public const SERVICE_TAG = 'funding.application.form_new_validator';

  public function validateInitial(
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram,
    ApplicationSchemaValidationResult $schemaValidationResult,
    bool $readOnly
  ): ApplicationFormValidationResult;

}
