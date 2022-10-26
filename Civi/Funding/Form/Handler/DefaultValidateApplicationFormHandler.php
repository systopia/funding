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

namespace Civi\Funding\Form\Handler;

use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

/**
 * @codeCoverageIgnore
 */
final class DefaultValidateApplicationFormHandler implements ValidateApplicationFormHandlerInterface {

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function handleValidateForm(ValidateApplicationFormEvent $event): void {
    $this->createHandler($event->getFundingCaseType()->getName())->handleValidateForm($event);
  }

  public function handleValidateNewForm(ValidateNewApplicationFormEvent $event): void {
    $this->createHandler($event->getFundingCaseType()->getName())->handleValidateNewForm($event);
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->serviceLocatorContainer->has($fundingCaseType);
  }

  private function createHandler(string $fundingCaseType): ValidateApplicationFormHandlerInterface {
    $serviceLocator = $this->serviceLocatorContainer->get($fundingCaseType);

    return new ValidateApplicationFormHandler(
      $serviceLocator->getApplicationFormFactory(),
      $serviceLocator->getFormValidator()
    );
  }

}
