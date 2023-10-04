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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\Form\ApplicationFormDataFactoryInterface;

final class ApplicationFormDataGetHandler implements ApplicationFormDataGetHandlerInterface {

  private ApplicationFormDataFactoryInterface $formDataFactory;

  private ApplicationFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationFormDataFactoryInterface $formDataFactory,
    ApplicationFormValidateHandlerInterface $validateHandler
  ) {
    $this->formDataFactory = $formDataFactory;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @inheritDoc
   */
  public function handle(ApplicationFormDataGetCommand $command): array {
    if ($command->hasFlag(ApplicationFormDataGetCommand::FLAG_COPY)) {
      $data = $this->formDataFactory->createFormDataForCopy(
        $command->getApplicationProcess(),
        $command->getFundingCase(),
      );
    }
    else {
      $data = $this->formDataFactory->createFormData(
        $command->getApplicationProcess(),
        $command->getFundingCase(),
      );
    }

    // Perform calculations
    $result = $this->validateHandler->handle(new ApplicationFormValidateCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
      $data,
      10
    ));

    return $result->getValidatedData()->getRawData();
  }

}
