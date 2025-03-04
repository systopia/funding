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
use Civi\Funding\ApplicationProcess\Helper\ApplicationJsonSchemaCreateHelper;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\RemoteTools\Form\RemoteForm;
use Civi\RemoteTools\Form\RemoteFormInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class ApplicationFormNewCreateHandler implements ApplicationFormNewCreateHandlerInterface {

  private ApplicationJsonSchemaCreateHelper $jsonSchemaCreateHelper;

  private NonCombinedApplicationJsonSchemaFactoryInterface $jsonSchemaFactory;

  private RequestContextInterface $requestContext;

  private ApplicationSubmitActionsFactoryInterface $submitActionsFactory;

  private NonCombinedApplicationUiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    ApplicationJsonSchemaCreateHelper $jsonSchemaCreateHelper,
    NonCombinedApplicationJsonSchemaFactoryInterface $jsonSchemaFactory,
    RequestContextInterface $requestContext,
    ApplicationSubmitActionsFactoryInterface $submitActionsFactory,
    NonCombinedApplicationUiSchemaFactoryInterface $uiSchemaFactory
  ) {
    $this->jsonSchemaCreateHelper = $jsonSchemaCreateHelper;
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->requestContext = $requestContext;
    $this->submitActionsFactory = $submitActionsFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  public function handle(ApplicationFormNewCreateCommand $command): RemoteFormInterface {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaInitial(
      $this->requestContext->getContactId(),
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
    );

    $this->jsonSchemaCreateHelper->addInitialActionProperty(
      $jsonSchema,
      $command->getFundingCaseType(),
      $command->getFundingProgram()->getPermissions()
    );

    $uiSchema = $this->uiSchemaFactory->createUiSchemaNew(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
    );

    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->createInitialSubmitActions($command->getFundingProgram()->getPermissions()),
    );
    $elements = array_merge($uiSchema->getElements(), $submitButtons);
    $uiSchema['elements'] = $elements;

    return new RemoteForm($jsonSchema, $uiSchema);
  }

}
