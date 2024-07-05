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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormNewValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class ApplicationFormNewValidateHandler implements ApplicationFormNewValidateHandlerInterface {

  private ApplicationFormNewCreateHandlerInterface $formCreateHandler;

  private ApplicationSchemaValidatorInterface $jsonSchemaValidator;

  private ApplicationFormNewValidatorInterface $formValidator;

  public function __construct(
    ApplicationFormNewCreateHandlerInterface $formNewCreateHandler,
    ApplicationFormNewValidatorInterface $formValidator,
    ApplicationSchemaValidatorInterface $jsonSchemaValidator
  ) {
    $this->formCreateHandler = $formNewCreateHandler;
    $this->formValidator = $formValidator;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  public function handle(ApplicationFormNewValidateCommand $command): ApplicationFormValidationResult {
    $form = $this->formCreateHandler->handle(new ApplicationFormNewCreateCommand(
      $command->getContactId(),
      $command->getFundingCaseType(),
      $command->getFundingProgram()
    ));
    $jsonSchema = $form->getJsonSchema();

    $schemaValidationResult = $this->jsonSchemaValidator->validate(
      $jsonSchema,
      $command->getData(),
      20
    );
    if (!$schemaValidationResult->isValid()) {
      return new ApplicationFormValidationResult(
        $schemaValidationResult->getLeafErrorMessages(),
        $schemaValidationResult->getData(),
        $schemaValidationResult->getCostItemsData(),
        $schemaValidationResult->getResourcesItemsData(),
        $schemaValidationResult->getTaggedData(),
        TRUE
      );
    }

    return $this->formValidator->validateInitial(
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
      $schemaValidationResult,
      $this->isJsonSchemaReadOnly($jsonSchema)
    );
  }

  private function isJsonSchemaReadOnly(JsonSchema $jsonSchema): bool {
    return TRUE === $jsonSchema->getKeywordValueOrDefault('readOnly', FALSE);
  }

}
