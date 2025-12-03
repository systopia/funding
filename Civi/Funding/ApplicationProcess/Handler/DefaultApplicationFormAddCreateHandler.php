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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddCreateCommand;
use Civi\Funding\Form\JsonFormsFormWithDataInterface;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

/**
 * @codeCoverageIgnore
 */
final class DefaultApplicationFormAddCreateHandler implements ApplicationFormAddCreateHandlerInterface {

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function handle(ApplicationFormAddCreateCommand $command): JsonFormsFormWithDataInterface {
    $handler = $this->serviceLocatorContainer->get($command->getFundingCaseType()->getName())
      ->getApplicationFormAddCreateHandler();
    if (NULL === $handler) {
      throw new \RuntimeException(sprintf(
        'Funding case type "%s" does not support combined applications',
        $command->getFundingCaseType()->getName()
      ));
    }

    return $handler->handle($command);
  }

}
