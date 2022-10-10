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
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

final class ApplicationFormFactoryCollection implements ApplicationFormFactoryInterface {

  private ContainerInterface $formFactoryLocator;

  public function __construct(ContainerInterface $formFactoryLocator) {
    $this->formFactoryLocator = $formFactoryLocator;
  }

  public function createForm(
    ApplicationProcessEntity $applicationProcess,
    FundingProgramEntity $fundingProgram,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationFormInterface {
    return $this->callCreateForm($fundingCaseType->getName(), __FUNCTION__, func_get_args());
  }

  public function createFormOnGet(GetApplicationFormEvent $event): ApplicationFormInterface {
    return $this->callCreateForm($event->getFundingCaseType()->getName(), __FUNCTION__, func_get_args());
  }

  public function createFormOnSubmit(SubmitApplicationFormEvent $event): ApplicationFormInterface {
    return $this->callCreateForm($event->getFundingCaseType()->getName(), __FUNCTION__, func_get_args());
  }

  public function createFormOnValidate(ValidateApplicationFormEvent $event): ApplicationFormInterface {
    return $this->callCreateForm($event->getFundingCaseType()->getName(), __FUNCTION__, func_get_args());
  }

  public function createValidatedData(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    Assert::true($validationResult->isValid(), 'Validation failed');

    return $this->callCreateValidatedData($fundingCaseType->getName(), __FUNCTION__, func_get_args());
  }

  public function createNewFormOnGet(GetNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->callCreateForm($event->getFundingCaseType()->getName(), __FUNCTION__, func_get_args());
  }

  public function createNewFormOnSubmit(SubmitNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->callCreateForm($event->getFundingCaseType()->getName(), __FUNCTION__, func_get_args());
  }

  public function createNewFormOnValidate(ValidateNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->callCreateForm($event->getFundingCaseType()->getName(), __FUNCTION__, func_get_args());
  }

  public function createNewValidatedData(
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    Assert::true($validationResult->isValid(), 'Validation failed');

    return $this->callCreateValidatedData($fundingCaseType->getName(), __FUNCTION__, func_get_args());
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->formFactoryLocator->has($fundingCaseType);
  }

  /**
   * @phpstan-param array<mixed> $args
   */
  private function callCreateForm(string $fundingCaseType, string $method, array $args): ApplicationFormInterface {
    $formFactory = $this->formFactoryLocator->get($fundingCaseType);

    /** @var \Civi\Funding\Form\ApplicationFormInterface */
    return call_user_func_array([$formFactory, $method], $args);
  }

  /**
   * @phpstan-param array<mixed> $args
   */
  private function callCreateValidatedData(
    string $fundingCaseType,
    string $method,
    array $args
  ): ValidatedApplicationDataInterface {
    $formFactory = $this->formFactoryLocator->get($fundingCaseType);

    /** @var \Civi\Funding\Form\ValidatedApplicationDataInterface */
    return call_user_func_array([$formFactory, $method], $args);
  }

}
