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

use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\FundingCase\FundingCaseJsonSchemaFactoryInterface;
use Civi\Funding\Form\FundingCase\FundingCaseUiSchemaFactoryInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFormDataGetCommand;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateGetCommand;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFormUpdateGetCommand
 */
final class FundingCaseFormUpdateGetHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseFormDataGetHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formDataGetHandlerMock;

  private FundingCaseFormUpdateGetHandler $handler;

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
    $this->formDataGetHandlerMock = $this->createMock(FundingCaseFormDataGetHandlerInterface::class);
    $this->jsonSchemaFactoryMock = $this->createMock(FundingCaseJsonSchemaFactoryInterface::class);
    $this->uiSchemaFactoryMock = $this->createMock(FundingCaseUiSchemaFactoryInterface::class);
    $this->handler = new FundingCaseFormUpdateGetHandler(
      $this->formDataGetHandlerMock,
      $this->jsonSchemaFactoryMock,
      $this->uiSchemaFactoryMock,
    );
  }

  public function testHandle(): void {
    $contactId = 12;
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $command = new FundingCaseFormUpdateGetCommand($contactId, $fundingProgram, $fundingCaseType, $fundingCase);

    $jsonSchema = new JsonSchema([]);
    $this->formDataGetHandlerMock->method('handle')
      ->with(new FundingCaseFormDataGetCommand($fundingProgram, $fundingCaseType, $fundingCase))
      ->willReturn(['foo' => 'bar']);
    $this->jsonSchemaFactoryMock->method('createJsonSchemaUpdate')
      ->with($fundingProgram, $fundingCaseType, $fundingCase)
      ->willReturn($jsonSchema);
    $uiSchema = new JsonFormsElement('test');
    $this->uiSchemaFactoryMock->method('createUiSchemaUpdate')
      ->with($fundingProgram, $fundingCaseType, $fundingCase)
      ->willReturn($uiSchema);

    $form = $this->handler->handle($command);
    static::assertSame($jsonSchema, $form->getJsonSchema());
    static::assertSame($uiSchema, $form->getUiSchema());
    static::assertSame(['foo' => 'bar'], $form->getData());
  }

}
