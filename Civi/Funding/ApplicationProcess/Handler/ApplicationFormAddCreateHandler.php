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
use Civi\Funding\ApplicationProcess\Helper\ApplicationJsonSchemaCreateHelper;
use Civi\Funding\Form\Application\CombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\CombinedApplicationUiSchemaFactoryInterface;
use Civi\RemoteTools\Form\RemoteForm;
use Civi\RemoteTools\Form\RemoteFormInterface;

final class ApplicationFormAddCreateHandler implements ApplicationFormAddCreateHandlerInterface {

  private ApplicationJsonSchemaCreateHelper $jsonSchemaCreateHelper;

  private CombinedApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private CombinedApplicationUiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    ApplicationJsonSchemaCreateHelper $jsonSchemaCreateHelper,
    CombinedApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    CombinedApplicationUiSchemaFactoryInterface $uiSchemaFactory
  ) {
    $this->jsonSchemaCreateHelper = $jsonSchemaCreateHelper;
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  public function handle(ApplicationFormAddCreateCommand $command): RemoteFormInterface {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaAdd(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
    );

    $this->jsonSchemaCreateHelper->addInitialActionProperty(
      $jsonSchema,
      $command->getFundingCaseType(),
      $command->getFundingCase()->getPermissions()
    );

    $uiSchema = $this->uiSchemaFactory->createUiSchemaAdd(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
    );

    return new RemoteForm($jsonSchema, $uiSchema);
  }

}
