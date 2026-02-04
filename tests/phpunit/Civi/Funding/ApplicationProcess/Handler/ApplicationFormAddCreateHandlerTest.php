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
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\Application\CombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\CombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\Translation\FormTranslatorInterface;
use Civi\Funding\Util\FormTestUtil;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormAddCreateCommand
 */
final class ApplicationFormAddCreateHandlerTest extends TestCase {

  private FormTranslatorInterface&MockObject $formTranslatorMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandler
   */
  private ApplicationFormAddCreateHandler $handler;

  private ApplicationJsonSchemaCreateHelper&MockObject $jsonSchemaCreateHelperMock;

  private CombinedApplicationJsonSchemaFactoryInterface&MockObject $jsonSchemaFactoryMock;

  private ApplicationSubmitActionsFactoryInterface&MockObject $submitActionsFactoryMock;

  private CombinedApplicationUiSchemaFactoryInterface&MockObject $uiSchemaFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->formTranslatorMock = $this->createMock(FormTranslatorInterface::class);
    $this->jsonSchemaCreateHelperMock = $this->createMock(ApplicationJsonSchemaCreateHelper::class);
    $this->jsonSchemaFactoryMock = $this->createMock(CombinedApplicationJsonSchemaFactoryInterface::class);
    $this->submitActionsFactoryMock = $this->createMock(ApplicationSubmitActionsFactoryInterface::class);
    $this->uiSchemaFactoryMock = $this->createMock(CombinedApplicationUiSchemaFactoryInterface::class);
    $this->handler = new ApplicationFormAddCreateHandler(
      $this->formTranslatorMock,
      $this->jsonSchemaCreateHelperMock,
      $this->jsonSchemaFactoryMock,
      $this->submitActionsFactoryMock,
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
    $uiSchema = new JsonFormsGroup('test', []);

    $this->jsonSchemaCreateHelperMock->expects(self::once())->method('addInitialActionProperty')
      ->with($jsonSchema, $fundingCaseType, $fundingCase->getPermissions());

    $this->uiSchemaFactoryMock->method('createUiSchemaAdd')
      ->with($fundingProgram, $fundingCaseType, $fundingCase)
      ->willReturn($uiSchema);

    $this->submitActionsFactoryMock->expects(self::once())->method('createInitialSubmitActions')
      ->with($fundingCase->getPermissions())
      ->willReturn([
        'submitAction1' => ['label' => 'Submit1', 'properties' => []],
        'submitAction2' => ['label' => 'Submit2', 'confirm' => 'Proceed?', 'properties' => []],
      ]);

    $this->formTranslatorMock->expects(self::once())->method('translateForm');

    $form = $this->handler->handle($command);
    static::assertSame($jsonSchema, $form->getJsonSchema());
    static::assertSame($uiSchema, $form->getUiSchema());
    static::assertSame([], $form->getData());

    static::assertEquals(
      [
        new JsonFormsSubmitButton('#/properties/_action', 'submitAction1', 'Submit1'),
        new JsonFormsSubmitButton('#/properties/_action', 'submitAction2', 'Submit2', 'Proceed?'),
      ],
      FormTestUtil::getControlsWithScope('#/properties/_action', $uiSchema)
    );
  }

}
