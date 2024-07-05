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
use Civi\Funding\ApplicationProcess\Command\ApplicationJsonSchemaGetCommand;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class ApplicationFormValidateHandler implements ApplicationFormValidateHandlerInterface {

  private ApplicationJsonSchemaGetHandlerInterface $jsonSchemaGetHandler;

  private ApplicationFormValidatorInterface $formValidator;

  private ApplicationSchemaValidatorInterface $jsonSchemaValidator;

  public function __construct(
    ApplicationJsonSchemaGetHandlerInterface $jsonSchemaGetHandler,
    ApplicationFormValidatorInterface $formValidator,
    ApplicationSchemaValidatorInterface $jsonSchemaValidator) {
    $this->jsonSchemaGetHandler = $jsonSchemaGetHandler;
    $this->formValidator = $formValidator;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  public function handle(ApplicationFormValidateCommand $command): ApplicationFormValidationResult {
    $jsonSchema = $this->jsonSchemaGetHandler->handle(new ApplicationJsonSchemaGetCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList()
    ));

    $schemaValidationResult = $this->jsonSchemaValidator->validate(
      $jsonSchema,
      $command->getData(),
      $command->getMaxErrors()
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

    return $this->formValidator->validateExisting(
      $command->getApplicationProcessBundle(),
      $schemaValidationResult,
      $this->isJsonSchemaReadOnly($jsonSchema)
    );
  }

  private function isJsonSchemaReadOnly(JsonSchema $jsonSchema): bool {
    return TRUE === $jsonSchema->getKeywordValueOrDefault('readOnly', FALSE);
  }

}
