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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddSubmitResult;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

/**
 * @codeCoverageIgnore
 */
final class DefaultApplicationFormAddSubmitHandler implements ApplicationFormAddSubmitHandlerInterface {

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function handle(ApplicationFormAddSubmitCommand $command): ApplicationFormAddSubmitResult {
    $handler = $this->serviceLocatorContainer->get($command->getFundingCaseType()->getName())
      ->getApplicationFormAddSubmitHandler();
    if (NULL === $handler) {
      throw new \RuntimeException(sprintf(
        'Funding case type "%s" does not support summary applications',
        $command->getFundingCaseType()->getName()
      ));
    }

    return $handler->handle($command);
  }

}
