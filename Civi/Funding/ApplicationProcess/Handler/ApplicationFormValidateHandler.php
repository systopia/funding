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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateResult;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Validation\ValidatorInterface;

final class ApplicationFormValidateHandler implements ApplicationFormValidateHandlerInterface {

  private ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private ValidatorInterface $validator;

  public function __construct(
    ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    ValidatorInterface $validator
  ) {
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->validator = $validator;
  }

  public function handle(ApplicationFormValidateCommand $command): ApplicationFormValidateResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaExisting(
      $command->getApplicationProcess(),
      $command->getFundingProgram(),
      $command->getFundingCase(),
      $command->getFundingCaseType(),
    );
    $validationResult = $this->validator->validate($jsonSchema, $command->getData(), 20);

    return ApplicationFormValidateResult::create($validationResult);
  }

}
