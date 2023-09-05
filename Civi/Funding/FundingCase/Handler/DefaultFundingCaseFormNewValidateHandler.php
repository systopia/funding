<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\Form\FundingCase\FundingCaseValidationResult;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewValidateCommand;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

/**
 * @codeCoverageIgnore
 */
final class DefaultFundingCaseFormNewValidateHandler implements FundingCaseFormNewValidateHandlerInterface {

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function handle(FundingCaseFormNewValidateCommand $command): FundingCaseValidationResult {
    $handler = $this->serviceLocatorContainer->get($command->getFundingCaseType()->getName())
      ->getFundingCaseFormNewValidateHandler();
    if (NULL === $handler) {
      throw new \RuntimeException(sprintf(
        'Funding case type "%s" does not allow create a funding case without application',
        $command->getFundingCaseType()->getName(),
      ));
    }

    return $handler->handle($command);
  }

}
