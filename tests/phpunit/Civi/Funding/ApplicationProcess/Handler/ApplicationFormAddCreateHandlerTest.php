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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddCreateCommand;
use Civi\Funding\ApplicationProcess\Helper\ApplicationJsonSchemaCreateHelper;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Form\Application\CombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\CombinedApplicationUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormAddCreateCommand
 */
final class ApplicationFormAddCreateHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandler
   */
  private ApplicationFormAddCreateHandler $handler;

  /**
   * @var \Civi\Funding\ApplicationProcess\Helper\ApplicationJsonSchemaCreateHelper&\PHPUnit\Framework\MockObject\MockObject
   */
  private $jsonSchemaCreateHelperMock;

  /**
   * @var \Civi\Funding\Form\Application\CombinedApplicationJsonSchemaFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $jsonSchemaFactoryMock;

  /**
   * @var \Civi\Funding\Form\Application\CombinedApplicationUiSchemaFactoryInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $uiSchemaFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->jsonSchemaCreateHelperMock = $this->createMock(ApplicationJsonSchemaCreateHelper::class);
    $this->jsonSchemaFactoryMock = $this->createMock(CombinedApplicationJsonSchemaFactoryInterface::class);
    $this->uiSchemaFactoryMock = $this->createMock(CombinedApplicationUiSchemaFactoryInterface::class);
    $this->handler = new ApplicationFormAddCreateHandler(
      $this->jsonSchemaCreateHelperMock,
      $this->jsonSchemaFactoryMock,
      $this->uiSchemaFactoryMock,
    );
  }

  public function testHandle(): void {
    $contactId = 12;
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $command = new ApplicationFormAddCreateCommand($contactId, $fundingProgram, $fundingCaseType, $fundingCase);

    $jsonSchema = new JsonSchema([]);
    $this->jsonSchemaFactoryMock->method('createJsonSchemaAdd')
      ->with($fundingProgram, $fundingCaseType, $fundingCase)
      ->willReturn($jsonSchema);
    $uiSchema = new JsonFormsElement('test');

    $this->jsonSchemaCreateHelperMock->expects(self::once())->method('addInitialActionProperty')
      ->with($jsonSchema, $fundingCaseType, $fundingCase->getPermissions());

    $this->uiSchemaFactoryMock->method('createUiSchemaAdd')
      ->with($fundingProgram, $fundingCaseType, $fundingCase)
      ->willReturn($uiSchema);

    $form = $this->handler->handle($command);
    static::assertSame($jsonSchema, $form->getJsonSchema());
    static::assertSame($uiSchema, $form->getUiSchema());
    static::assertSame([], $form->getData());
  }

}
