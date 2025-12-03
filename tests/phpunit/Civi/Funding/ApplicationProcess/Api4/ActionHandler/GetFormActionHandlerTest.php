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

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetFormAction;
use Civi\Funding\ApplicationProcess\Api4\ActionHandler\GetFormActionHandler;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Form\JsonFormsFormWithData;
use Civi\Funding\Traits\CreateMockTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Api4\ActionHandler\GetFormActionHandler
 */
final class GetFormActionHandlerTest extends TestCase {

  use CreateMockTrait;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessBundleLoaderMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formCreateHandlerMock;

  private GetFormActionHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->formCreateHandlerMock = $this->createMock(ApplicationFormCreateHandlerInterface::class);
    $this->handler = new GetFormActionHandler(
      $this->applicationProcessBundleLoaderMock,
      $this->formCreateHandlerMock
    );
  }

  public function testGetForm(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $statusList = [123 => new FullApplicationProcessStatus('test', NULL, NULL)];

    $applicationProcessId = $applicationProcessBundle->getApplicationProcess()->getId();

    $action = $this->createApi4ActionMock(GetFormAction::class)
      ->setId($applicationProcessId);

    $this->applicationProcessBundleLoaderMock->method('get')
      ->with($applicationProcessId)
      ->willReturn($applicationProcessBundle);
    $this->applicationProcessBundleLoaderMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($statusList);

    $this->formCreateHandlerMock->method('handle')
      ->with(new ApplicationFormCreateCommand($applicationProcessBundle, $statusList))
      ->willReturn(new JsonFormsFormWithData(new JsonSchema(['foo' => 'bar']),
        new JsonFormsElement('test'),
        ['x' => 'y']
      ));

    static::assertSame([
      'jsonSchema' => ['foo' => 'bar'],
      'uiSchema' => ['type' => 'test'],
      'data' => ['x' => 'y'],
    ], $this->handler->getForm($action));
  }

}
