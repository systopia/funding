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

use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

/**
 * @codeCoverageIgnore
 */
final class DefaultGetApplicationFormHandler implements GetApplicationFormHandlerInterface {

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function handleGetForm(GetApplicationFormEvent $event): void {
    $this->createHander($event->getFundingCaseType()->getName())->handleGetForm($event);
  }

  public function handleGetNewForm(GetNewApplicationFormEvent $event): void {
    $this->createHander($event->getFundingCaseType()->getName())->handleGetNewForm($event);
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->serviceLocatorContainer->has($fundingCaseType);
  }

  private function createHander(string $fundingCaseType): GetApplicationFormHandlerInterface {
    return new GetApplicationFormHandler(
      $this->serviceLocatorContainer->get($fundingCaseType)->getApplicationFormFactory()
    );
  }

}
