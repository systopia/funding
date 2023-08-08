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
use Civi\Funding\Form\NonSummaryApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\NonSummaryApplicationUiSchemaFactoryInterface;
use Civi\RemoteTools\Form\RemoteForm;
use Civi\RemoteTools\Form\RemoteFormInterface;

final class ApplicationFormNewCreateHandler implements ApplicationFormNewCreateHandlerInterface {

  private NonSummaryApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private NonSummaryApplicationUiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    NonSummaryApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    NonSummaryApplicationUiSchemaFactoryInterface $uiSchemaFactory
  ) {
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  public function handle(ApplicationFormNewCreateCommand $command): RemoteFormInterface {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaInitial(
      $command->getContactId(),
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
    );
    $uiSchema = $this->uiSchemaFactory->createUiSchemaNew(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
    );

    return new RemoteForm($jsonSchema, $uiSchema);
  }

}
