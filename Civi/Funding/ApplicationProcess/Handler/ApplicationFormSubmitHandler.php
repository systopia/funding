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

use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;

final class ApplicationFormSubmitHandler implements ApplicationFormSubmitHandlerInterface {

  private ApplicationActionApplyHandlerInterface $actionApplyHandler;

  private ApplicationFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationActionApplyHandlerInterface $actionApplyHandler,
    ApplicationFormValidateHandlerInterface $validateHandler
  ) {
    $this->actionApplyHandler = $actionApplyHandler;
    $this->validateHandler = $validateHandler;
  }

  public function handle(ApplicationFormSubmitCommand $command): ApplicationFormSubmitResult {
    $validationResult = $this->validateHandler->handle(new ApplicationFormValidateCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
      $command->getData(),
    ));

    if (!$validationResult->isValid()) {
      return ApplicationFormSubmitResult::createError($validationResult);
    }

    $this->actionApplyHandler->handle(new ApplicationActionApplyCommand(
      $command->getContactId(),
      $validationResult->getValidatedData()->getAction(),
      $command->getApplicationProcessBundle(),
      $validationResult,
    ));

    return ApplicationFormSubmitResult::createSuccess($validationResult);
  }

}
