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

use Civi\Funding\Form\FundingCase\FundingCaseJsonSchemaFactoryInterface;
use Civi\Funding\Form\FundingCase\FundingCaseUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonFormsFormWithData;
use Civi\Funding\Form\JsonFormsFormWithDataInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFormDataGetCommand;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateGetCommand;

final class FundingCaseFormUpdateGetHandler implements FundingCaseFormUpdateGetHandlerInterface {

  private FundingCaseFormDataGetHandlerInterface $formDataGetHandler;

  private FundingCaseJsonSchemaFactoryInterface $jsonSchemaFactory;

  private FundingCaseUiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    FundingCaseFormDataGetHandlerInterface $formDataGetHandler,
    FundingCaseJsonSchemaFactoryInterface $jsonSchemaFactory,
    FundingCaseUiSchemaFactoryInterface $uiSchemaFactory
  ) {
    $this->formDataGetHandler = $formDataGetHandler;
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  public function handle(FundingCaseFormUpdateGetCommand $command): JsonFormsFormWithDataInterface {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaUpdate(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
    );
    $uiSchema = $this->uiSchemaFactory->createUiSchemaUpdate(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
    );
    $data = $this->formDataGetHandler->handle(new FundingCaseFormDataGetCommand(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
    ));

    return new JsonFormsFormWithData($jsonSchema, $uiSchema, $data);
  }

}
