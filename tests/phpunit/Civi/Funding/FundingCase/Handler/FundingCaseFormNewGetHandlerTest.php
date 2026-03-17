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
use Civi\Funding\Mock\RequestContext\TestRequestContext;
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

  private FundingCaseJsonSchemaFactoryInterface&MockObject $jsonSchemaFactoryMock;

  private TestRequestContext $requestContext;

  private FundingCaseUiSchemaFactoryInterface&MockObject $uiSchemaFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->jsonSchemaFactoryMock = $this->createMock(FundingCaseJsonSchemaFactoryInterface::class);
    $this->requestContext = TestRequestContext::newInternal(111);
    $this->uiSchemaFactoryMock = $this->createMock(FundingCaseUiSchemaFactoryInterface::class);
    $this->handler = new FundingCaseFormNewGetHandler(
      $this->jsonSchemaFactoryMock,
      $this->requestContext,
      $this->uiSchemaFactoryMock,
    );
  }

  public function testHandle(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $command = new FundingCaseFormNewGetCommand($fundingProgram, $fundingCaseType);

    $jsonSchema = new JsonSchema([]);
    $this->jsonSchemaFactoryMock->method('createJsonSchemaNew')
      ->with($this->requestContext->getContactId(), $fundingProgram, $fundingCaseType)
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
