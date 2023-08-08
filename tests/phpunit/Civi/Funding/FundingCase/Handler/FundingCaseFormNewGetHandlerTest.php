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

use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\FundingCase\FundingCaseJsonSchemaFactoryInterface;
use Civi\Funding\Form\FundingCase\FundingCaseUiSchemaFactoryInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewGetCommand;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFormNewGetHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormNewGetCommand
 */
final class FundingCaseFormNewGetHandlerTest extends TestCase {

  private FundingCaseFormNewGetHandler $handler;

  /**
   * @var \Civi\Funding\Form\FundingCase\FundingCaseJsonSchemaFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaFactoryMock;

  /**
   * @var \Civi\Funding\Form\FundingCase\FundingCaseUiSchemaFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $uiSchemaFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->jsonSchemaFactoryMock = $this->createMock(FundingCaseJsonSchemaFactoryInterface::class);
    $this->uiSchemaFactoryMock = $this->createMock(FundingCaseUiSchemaFactoryInterface::class);
    $this->handler = new FundingCaseFormNewGetHandler(
      $this->jsonSchemaFactoryMock,
      $this->uiSchemaFactoryMock,
    );
  }

  public function testHandle(): void {
    $contactId = 12;
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $command = new FundingCaseFormNewGetCommand($contactId, $fundingProgram, $fundingCaseType);

    $jsonSchema = new JsonSchema([]);
    $this->jsonSchemaFactoryMock->method('createJsonSchemaNew')
      ->with($contactId, $fundingProgram, $fundingCaseType)
      ->willReturn($jsonSchema);
    $uiSchema = new JsonFormsElement('test');
    $this->uiSchemaFactoryMock->method('createUiSchemaNew')
      ->with($fundingProgram, $fundingCaseType)
      ->willReturn($uiSchema);

    $form = $this->handler->handle($command);
    static::assertSame($jsonSchema, $form->getJsonSchema());
    static::assertSame($uiSchema, $form->getUiSchema());
  }

}
