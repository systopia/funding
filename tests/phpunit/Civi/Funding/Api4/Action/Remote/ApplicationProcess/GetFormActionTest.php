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
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Event\Remote\ApplicationProcess\GetFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction
 * @covers \Civi\Funding\Event\Remote\ApplicationProcess\GetFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingGetFormEvent
 */
final class GetFormActionTest extends TestCase {

  private GetFormAction $action;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var array<string, mixed>
   */
  private array $applicationProcess;

  /**
   * @var array<string, mixed>
   */
  private array $fundingCase;

  /**
   * @var array<string, mixed>
   */
  private array $fundingCaseType;

  protected function setUp(): void {
    parent::setUp();
    $remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->action = new GetFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setApplicationProcessId(22);

    $this->applicationProcess = ['id' => 22, 'funding_case_id' => 33];
    $this->fundingCase = ['id' => 33, 'funding_case_type_id' => 44];
    $this->fundingCaseType = ['id' => 44];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingApplicationProcess', 22, '00', $this->applicationProcess],
      ['FundingCase', 33, '00', $this->fundingCase],
      ['FundingCaseType', 44, '00', $this->fundingCaseType],
    ]);
  }

  public function testRun(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          GetFormEvent::getEventName('RemoteFundingApplicationProcess', 'getForm'),
          static::callback(
            function (GetFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->applicationProcess, $event->getApplicationProcess());
              static::assertSame($this->fundingCase, $event->getFundingCase());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());

              $event->setJsonSchema(['type' => 'object']);
              $event->setUiSchema(['type' => 'Group']);
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
      'jsonSchema' => ['type' => 'object'],
      'uiSchema' => ['type' => 'Group'],
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