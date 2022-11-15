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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\ApplicationUiSchemaFactoryInterface;
use Civi\RemoteTools\Form\RemoteForm;
use Civi\RemoteTools\Form\RemoteFormInterface;

final class ApplicationFormCreateHandler implements ApplicationFormCreateHandlerInterface {

  private ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private ApplicationUiSchemaFactoryInterface $uiSchemaFactory;

  private ApplicationFormDataGetHandlerInterface $dataGetHandler;

  public function __construct(
    ApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    ApplicationUiSchemaFactoryInterface $uiSchemaFactory,
    ApplicationFormDataGetHandlerInterface $dataGetHandler
  ) {
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
    $this->dataGetHandler = $dataGetHandler;
  }

  public function handle(ApplicationFormCreateCommand $command): RemoteFormInterface {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaExisting(
      $command->getApplicationProcess(),
      $command->getFundingProgram(),
      $command->getFundingCase(),
      $command->getFundingCaseType(),
    );
    $uiSchema = $this->uiSchemaFactory->createUiSchemaExisting(
      $command->getApplicationProcess(),
      $command->getFundingProgram(),
      $command->getFundingCase(),
      $command->getFundingCaseType(),
    );

    $data = $command->getData();
    if (NULL === $data) {
      $data = $this->dataGetHandler->handle(new ApplicationFormDataGetCommand(
        $command->getApplicationProcess(),
        $command->getFundingCase(),
        $command->getFundingCaseType(),
        $command->getFundingProgram(),
      ));
    }

    $data['applicationProcessId'] = $command->getApplicationProcess()->getId();

    return new RemoteForm($jsonSchema, $uiSchema, $data);
  }

}
