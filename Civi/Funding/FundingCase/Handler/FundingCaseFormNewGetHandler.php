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
use Civi\Funding\FundingCase\Command\FundingCaseFormNewGetCommand;
use Civi\RemoteTools\Form\RemoteForm;
use Civi\RemoteTools\Form\RemoteFormInterface;

final class FundingCaseFormNewGetHandler implements FundingCaseFormNewGetHandlerInterface {

  private FundingCaseJsonSchemaFactoryInterface $jsonSchemaFactory;

  private FundingCaseUiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    FundingCaseJsonSchemaFactoryInterface $jsonSchemaFactory,
    FundingCaseUiSchemaFactoryInterface $uiSchemaFactory
  ) {
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  public function handle(FundingCaseFormNewGetCommand $command): RemoteFormInterface {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchemaNew(
      $command->getContactId(),
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
    );
    $uiSchema = $this->uiSchemaFactory->createUiSchemaNew(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
    );

    return new RemoteForm($jsonSchema, $uiSchema);
  }

}
