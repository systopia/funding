<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Form;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\Validation\ValidationResult;

interface ApplicationFormFactoryInterface {

  /**
   * @phpstan-return array<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  public function createForm(
    ApplicationProcessEntity $applicationProcess,
    FundingProgramEntity $fundingProgram,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationFormInterface;

  public function createFormOnGet(GetApplicationFormEvent $event): ApplicationFormInterface;

  public function createFormOnSubmit(SubmitApplicationFormEvent $event): ApplicationFormInterface;

  public function createFormOnValidate(ValidateApplicationFormEvent $event): ApplicationFormInterface;

  /**
   * Maybe put somewhere else?
   *
   * @param \Civi\Funding\Form\Validation\ValidationResult $validationResult
   *   MUST be valid result.
   */
  public function createValidatedData(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface;

  public function createNewFormOnGet(GetNewApplicationFormEvent $event): ApplicationFormInterface;

  public function createNewFormOnSubmit(SubmitNewApplicationFormEvent $event): ApplicationFormInterface;

  public function createNewFormOnValidate(ValidateNewApplicationFormEvent $event): ApplicationFormInterface;

  /**
   * Maybe put somewhere else?
   *
   * @param \Civi\Funding\Form\Validation\ValidationResult $validationResult
   *   MUST be valid result.
   */
  public function createNewValidatedData(
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface;

  public function supportsFundingCaseType(string $fundingCaseType): bool;

}
