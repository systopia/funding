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

/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\Generic\Result;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Traits\CreateMockTrait;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction
 * @covers \Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingGetFormEvent
 */
final class GetFormActionTest extends AbstractFormActionTest {

  use CreateMockTrait;

  private GetFormAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->action = $this->createApi4ActionMock(
      GetFormAction::class,
      $this->applicationProcessBundleLoaderMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId(static::REMOTE_CONTACT_ID);
    $this->action->setExtraParam('contactId', static::CONTACT_ID);
    $this->action->setApplicationProcessId($this->applicationProcessBundle->getApplicationProcess()->getId());
  }

  public function testRun(): void {
    $jsonSchema = new JsonSchema(['foo' => 'test']);
    $uiSchema = new JsonFormsElement('Test');
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          GetApplicationFormEvent::getEventName('RemoteFundingApplicationProcess', 'getForm'),
          static::callback(
            function (GetApplicationFormEvent $event) use ($jsonSchema, $uiSchema): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->applicationProcessBundle, $event->getApplicationProcessBundle());

              $event->setJsonSchema($jsonSchema);
              $event->setUiSchema($uiSchema);
              $event->setData(['foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          GetApplicationFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(GetApplicationFormEvent::class),
        ],
        [
          GetApplicationFormEvent::getEventName(),
          static::isInstanceOf(GetApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'jsonSchema' => $jsonSchema,
      'uiSchema' => $uiSchema,
      'data' => ['foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testNoEventListener(): void {
    static::expectExceptionObject(new FundingException(
      'Application process with ID "22" not found',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
