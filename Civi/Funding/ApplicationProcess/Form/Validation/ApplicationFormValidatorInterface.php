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
use Civi\Funding\Entity\ApplicationProcessEntityBundle;

/**
 * Used when validating a submit to an existing application, i.e. for combined
 * and non-combined applications.
 *
 * This can be used if additional validation to JSON schema validation is
 * required or if additional mapped values shall be set.
 *
 * Implementations should be registered as service tagged with SERVICE_TAG and
 * add the public static method getFundingCaseType() or getFundingCaseTypes().
 */
interface ApplicationFormValidatorInterface {

  public const SERVICE_TAG = 'funding.application.form_validator';

  public function validateExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationSchemaValidationResult $schemaValidationResult,
    bool $readOnly
  ): ApplicationFormValidationResult;

}
