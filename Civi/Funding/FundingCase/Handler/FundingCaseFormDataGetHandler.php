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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\Form\FundingCase\FundingCaseFormDataFactoryInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFormDataGetCommand;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;

final class FundingCaseFormDataGetHandler implements FundingCaseFormDataGetHandlerInterface {

  private FundingCaseFormDataFactoryInterface $formDataFactory;

  private FundingCaseFormUpdateValidateHandlerInterface $validateHandler;

  public function __construct(
    FundingCaseFormDataFactoryInterface $formDataFactory,
    FundingCaseFormUpdateValidateHandlerInterface $validateHandler
  ) {
    $this->formDataFactory = $formDataFactory;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @inheritDoc
   */
  public function handle(FundingCaseFormDataGetCommand $command): array {
    $data = $this->formDataFactory->createFormData($command->getFundingCase());

    // Perform calculations
    $result = $this->validateHandler->handle(new FundingCaseFormUpdateValidateCommand(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
      $data,
    ));

    return $result->getValidatedData()->getRawData();
  }

}
