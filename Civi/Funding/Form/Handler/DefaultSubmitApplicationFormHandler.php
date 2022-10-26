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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

/**
 * @codeCoverageIgnore
 */
final class DefaultSubmitApplicationFormHandler implements SubmitApplicationFormHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function handleSubmitForm(SubmitApplicationFormEvent $event): void {
    $this->createHandler($event->getFundingCaseType()->getName())->handleSubmitForm($event);
  }

  public function handleSubmitNewForm(SubmitNewApplicationFormEvent $event): void {
    $this->createHandler($event->getFundingCaseType()->getName())->handleSubmitNewForm($event);
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->serviceLocatorContainer->has($fundingCaseType);
  }

  private function createHandler(string $fundingCaseType): SubmitApplicationFormHandlerInterface {
    $serviceLocator = $this->serviceLocatorContainer->get($fundingCaseType);

    return new SubmitApplicationFormHandler(
      $this->applicationProcessManager,
      $serviceLocator->getApplicationFormFactory(),
      $this->fundingCaseManager,
      $serviceLocator->getApplicationProcessStatusDeterminer(),
      $serviceLocator->getFormValidator(),
    );
  }

}
