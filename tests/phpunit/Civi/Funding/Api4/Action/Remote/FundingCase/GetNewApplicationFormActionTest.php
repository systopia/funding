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

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\Event\RemoteFundingCaseGetNewApplicationFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction
 * @covers \Civi\Funding\Event\RemoteFundingCaseGetNewApplicationFormEvent
 * @covers \Civi\Funding\Event\AbstractRemoteFundingGetFormEvent
 */
final class RemoteFundingCaseGetNewApplicationFormActionTest extends TestCase {

  private GetNewApplicationFormAction $action;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var array<string, mixed>
   */
  private array $fundingCaseType;

  /**
   * @var array<string, mixed>
   */
  private array $fundingProgram;

  protected function setUp(): void {
    parent::setUp();
    $remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->action = new GetNewApplicationFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setFundingCaseTypeId(22);
    $this->action->setFundingProgramId(33);

    $this->fundingCaseType = ['id' => 22];
    $this->fundingProgram = ['id' => 33];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingCaseType', 22, '00', $this->fundingCaseType],
      ['FundingProgram', 33, '00', $this->fundingProgram],
    ]);
  }

  public function testRun(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingCaseGetNewApplicationFormEvent::getEventName('RemoteFundingCase', 'getNewApplicationForm'),
          static::callback(
            function (RemoteFundingCaseGetNewApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->setJsonSchema(['type' => 'object']);
              $event->setUiSchema(['type' => 'Group']);
              $event->setData(['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          RemoteFundingCaseGetNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(RemoteFundingCaseGetNewApplicationFormEvent::class),
        ],
        [
          RemoteFundingCaseGetNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingCaseGetNewApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'jsonSchema' => ['type' => 'object'],
      'uiSchema' => ['type' => 'Group'],
      'data' => ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testNoEventListener(): void {
    static::expectExceptionObject(new \API_Exception(
      'Invalid fundingProgramId or fundingCaseTypeId',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
