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
use Civi\Funding\ApplicationProcess\Command\ApplicationJsonSchemaGetCommand;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\Application\ApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\Form\JsonFormsFormWithData;
use Civi\Funding\Form\JsonFormsFormWithDataInterface;
use Civi\Funding\Translation\FormTranslatorInterface;

final class ApplicationFormCreateHandler implements ApplicationFormCreateHandlerInterface {

  private FormTranslatorInterface $formTranslator;

  private ApplicationFormDataGetHandlerInterface $dataGetHandler;

  private ApplicationJsonSchemaGetHandlerInterface $jsonSchemaGetHandler;

  private ApplicationSubmitActionsFactoryInterface $submitActionsFactory;

  private ApplicationUiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    FormTranslatorInterface $formTranslator,
    ApplicationFormDataGetHandlerInterface $dataGetHandler,
    ApplicationJsonSchemaGetHandlerInterface $jsonSchemaGetHandler,
    ApplicationSubmitActionsFactoryInterface $submitActionsFactory,
    ApplicationUiSchemaFactoryInterface $uiSchemaFactory,
  ) {
    $this->dataGetHandler = $dataGetHandler;
    $this->formTranslator = $formTranslator;
    $this->jsonSchemaGetHandler = $jsonSchemaGetHandler;
    $this->submitActionsFactory = $submitActionsFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationFormCreateCommand $command): JsonFormsFormWithDataInterface {
    $jsonSchema = $this->jsonSchemaGetHandler->handle(new ApplicationJsonSchemaGetCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
    ));
    $uiSchema = $this->uiSchemaFactory->createUiSchemaExisting(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
    );

    $submitButtons = JsonFormsSubmitButtonsFactory::createButtons(
      $this->submitActionsFactory->getSubmitActions(
        $command->getApplicationProcessBundle(),
        $command->getApplicationProcessStatusList()
      )
    );
    $elements = array_merge($uiSchema->getElements(), $submitButtons);
    $uiSchema['elements'] = $elements;

    if (TRUE === $jsonSchema['readOnly']) {
      $uiSchema->setReadonly(TRUE);
    }

    $data = $this->dataGetHandler->handle(new ApplicationFormDataGetCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
    ));

    $form = new JsonFormsFormWithData($jsonSchema, $uiSchema, $data);
    $this->formTranslator->translateForm($form, $command->getFundingProgram(), $command->getFundingCaseType());

    return $form;
  }

}
