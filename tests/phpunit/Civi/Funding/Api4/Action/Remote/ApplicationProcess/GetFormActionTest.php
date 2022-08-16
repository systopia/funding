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
use Civi\Funding\Event\Remote\ApplicationProcess\GetFormEvent;
use Civi\Funding\Form\JsonForms\JsonFormsElement;
use Civi\Funding\Form\JsonSchema\JsonSchema;
use Webmozart\Assert\Assert;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction
 * @covers \Civi\Funding\Event\Remote\ApplicationProcess\GetFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingGetFormEvent
 */
final class GetFormActionTest extends AbstractFormActionTest {

  private GetFormAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->action = new GetFormAction(
      $this->remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId(static::REMOTE_CONTACT_ID);
    $this->action->setExtraParam('contactId', static::CONTACT_ID);
    Assert::integer($this->applicationProcess['id']);
    $this->action->setApplicationProcessId($this->applicationProcess['id']);
  }

  public function testRun(): void {
    $jsonSchema = new JsonSchema(['foo' => 'test']);
    $uiSchema = new JsonFormsElement('Test');
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          GetFormEvent::getEventName('RemoteFundingApplicationProcess', 'getForm'),
          static::callback(
            function (GetFormEvent $event) use ($jsonSchema, $uiSchema): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->applicationProcess, $event->getApplicationProcess()->toArray());
              static::assertSame($this->fundingCase, $event->getFundingCase()->toArray());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->setJsonSchema($jsonSchema);
              $event->setUiSchema($uiSchema);
              $event->setData(['applicationProcessId' => 22, 'foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          GetFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(GetFormEvent::class),
        ],
        [
          GetFormEvent::getEventName(),
          static::isInstanceOf(GetFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'jsonSchema' => $jsonSchema,
      'uiSchema' => $uiSchema,
      'data' => ['applicationProcessId' => 22, 'foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testNoEventListener(): void {
    static::expectExceptionObject(new \API_Exception(
      'Invalid applicationProcessId',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
