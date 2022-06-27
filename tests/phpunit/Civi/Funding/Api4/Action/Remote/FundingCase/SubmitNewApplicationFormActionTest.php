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
 * @noinspection PropertyAnnotationInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction;
use Civi\Funding\Event\RemoteFundingCaseSubmitNewApplicationFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction
 * @covers \Civi\Funding\Event\RemoteFundingCaseSubmitNewApplicationFormEvent
 * @covers \Civi\Funding\Event\AbstractRemoteFundingSubmitFormEvent
 */
final class RemoteFundingCaseSubmitNewApplicationFormActionTest extends TestCase {

  private SubmitNewApplicationFormAction $action;


  /**
   * @var array<string, mixed>
   */
  private array $data;

  /**
   * @var \Civi\Core\CiviEventDispatcher&\PHPUnit\Framework\MockObject\MockObject
   */
  private $eventDispatcherMock;

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
    $this->action = new SubmitNewApplicationFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->data = [
      'fundingCaseTypeId' => 22,
      'fundingProgramId' => 33,
    ];
    $this->action->setData($this->data);

    $this->fundingCaseType = ['id' => 22];
    $this->fundingProgram = ['id' => 33];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingCaseType', 22, '00', $this->fundingCaseType],
      ['FundingProgram', 33, '00', $this->fundingProgram],
    ]);
  }

  public function testShowValidation(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'submitNewApplicationForm'
          ),
          static::callback(
            function (RemoteFundingCaseSubmitNewApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->data, $event->getData());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->addError('/foo', 'Bar');

              return TRUE;
            }),
        ],
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(RemoteFundingCaseSubmitNewApplicationFormEvent::class),
        ],
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingCaseSubmitNewApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'action' => 'showValidation',
      'errors' => ['/foo' => ['Bar']],
    ], $result->getArrayCopy());
  }

  public function testShowForm(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'submitNewApplicationForm'
          ),
          static::callback(
            function (RemoteFundingCaseSubmitNewApplicationFormEvent $event): bool {
              $jsonSchema = ['type' => 'object'];
              $uiSchema = ['type' => 'Group'];
              $data = ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'];
              $event->setForm($jsonSchema, $uiSchema, $data);
              $event->setMessage('Test');

              return TRUE;
            }),
        ],
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(RemoteFundingCaseSubmitNewApplicationFormEvent::class),
        ],
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingCaseSubmitNewApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'action' => 'showForm',
      'message' => 'Test',
      'jsonSchema' => ['type' => 'object'],
      'uiSchema' => ['type' => 'Group'],
      'data' => ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testCloseForm(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'submitNewApplicationForm'
          ),
          static::callback(
            function (RemoteFundingCaseSubmitNewApplicationFormEvent $event): bool {
              $event->setAction(RemoteFundingCaseSubmitNewApplicationFormEvent::ACTION_CLOSE_FORM);
              $event->setMessage('Test');

              return TRUE;
            }),
        ],
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(RemoteFundingCaseSubmitNewApplicationFormEvent::class),
        ],
        [
          RemoteFundingCaseSubmitNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingCaseSubmitNewApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'action' => 'closeForm',
      'message' => 'Test',
    ], $result->getArrayCopy());
  }

  public function testNoAction(): void {
    $this->expectException(\API_Exception::class);
    $this->expectExceptionMessage('Form not handled');

    $result = new Result();
    $this->action->_run($result);
  }

}
