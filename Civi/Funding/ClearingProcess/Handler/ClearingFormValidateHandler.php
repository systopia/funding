<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Handler;

use Civi\Funding\ClearingProcess\Command\ClearingFormGetCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormValidateCommand;
use Civi\Funding\ClearingProcess\Form\Validation\ClearingFormValidationResult;
use Civi\Funding\ClearingProcess\Form\Validation\ClearingFormValidatorInterface;
use Civi\Funding\ClearingProcess\JsonSchema\Validator\ClearingSchemaValidator;

final class ClearingFormValidateHandler implements ClearingFormValidateHandlerInterface {

  private ClearingFormGetHandlerInterface $formGetHandler;

  private ClearingFormValidatorInterface $formValidator;

  private ClearingSchemaValidator $jsonSchemaValidator;

  public function __construct(
    ClearingFormGetHandlerInterface $formGetHandler,
    ClearingFormValidatorInterface $formValidator,
    ClearingSchemaValidator $jsonSchemaValidator) {
    $this->formGetHandler = $formGetHandler;
    $this->formValidator = $formValidator;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  public function handle(ClearingFormValidateCommand $command): ClearingFormValidationResult {
    $form = $this->formGetHandler->handle(new ClearingFormGetCommand($command->getClearingProcessBundle()));

    $schemaValidationResult = $this->jsonSchemaValidator->validate(
      $form->getJsonSchema(),
      $command->getData(),
      $command->getMaxErrors()
    );
    if (!$schemaValidationResult->isValid()) {
      return new ClearingFormValidationResult(
        $schemaValidationResult->getLeafErrorMessages(),
        $schemaValidationResult->getData(),
        $schemaValidationResult->getTaggedData()
      );
    }

    return $this->formValidator->validate(
      $command->getClearingProcessBundle(),
      $schemaValidationResult->getData(),
      $schemaValidationResult->getTaggedData()
    );
  }

}
