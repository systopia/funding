<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingClearingProcess\GetFormAction;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\Command\ClearingFormDataGetCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormGetCommand;
use Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandlerInterface;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Traits\CreateMockTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\GetFormActionHandler
 */
final class GetFormActionHandlerTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingProcessBundleLoaderMock;

  /**
   * @var \Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formDataGetHandlerMock;

  /**
   * @var \Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formGetHandlerMock;

  private GetFormActionHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->clearingProcessBundleLoaderMock = $this->createMock(ClearingProcessBundleLoader::class);
    $this->formDataGetHandlerMock = $this->createMock(ClearingFormDataGetHandlerInterface::class);
    $this->formGetHandlerMock = $this->createMock(ClearingFormGetHandlerInterface::class);
    $this->handler = new GetFormActionHandler(
      $this->clearingProcessBundleLoaderMock,
      $this->formDataGetHandlerMock,
      $this->formGetHandlerMock
    );
  }

  public function testGetForm(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $clearingProcessId = $clearingProcessBundle->getClearingProcess()->getId();

    $action = $this->createApi4ActionMock(GetFormAction::class)
      ->setId($clearingProcessId);

    $this->clearingProcessBundleLoaderMock->method('get')->with($clearingProcessId)
      ->willReturn($clearingProcessBundle);

    $this->formDataGetHandlerMock->method('handle')
      ->with(new ClearingFormDataGetCommand($clearingProcessBundle))
      ->willReturn(['x' => 'y']);

    $this->formGetHandlerMock->method('handle')
      ->with(new ClearingFormGetCommand($clearingProcessBundle))
      ->willReturn(new JsonFormsForm(new JsonSchema(['foo' => 'bar']), new JsonFormsElement('test')));

    static::assertSame([
      'jsonSchema' => ['foo' => 'bar'],
      'uiSchema' => ['type' => 'test'],
      'data' => ['x' => 'y'],
    ], $this->handler->getForm($action));
  }

}
