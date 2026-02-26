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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddValidateCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormAddValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class ApplicationFormAddValidateHandler implements ApplicationFormAddValidateHandlerInterface {

  private ApplicationFormAddCreateHandlerInterface $formCreateHandler;

  private ApplicationFormAddValidatorInterface $formValidator;

  private ApplicationSchemaValidatorInterface $jsonSchemaValidator;

  public function __construct(
    ApplicationFormAddCreateHandlerInterface $formAddCreateHandler,
    ApplicationFormAddValidatorInterface $formValidator,
    ApplicationSchemaValidatorInterface $jsonSchemaValidator
  ) {
    $this->formCreateHandler = $formAddCreateHandler;
    $this->formValidator = $formValidator;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  public function handle(ApplicationFormAddValidateCommand $command): ApplicationFormValidationResult {
    $form = $this->formCreateHandler->handle(new ApplicationFormAddCreateCommand(
      $command->getContactId(),
      $command->getFundingCaseBundle()
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

    return $this->formValidator->validateAdd(
      $command->getFundingCase(),
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
