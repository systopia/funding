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
use Civi\Funding\Event\RemoteFundingApplicationProcessValidateFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\RemoteFundingApplicationProcessValidateFormAction
 * @covers \Civi\Funding\Event\RemoteFundingApplicationProcessValidateFormEvent
 * @covers \Civi\Funding\Event\AbstractRemoteFundingValidateFormEvent
 */
final class RemoteFundingApplicationProcessValidateFormActionTest extends TestCase {

  private RemoteFundingApplicationProcessValidateFormAction $action;

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
    $this->action = new RemoteFundingApplicationProcessValidateFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->data = ['applicationProcessId' => 22];
    $this->action->setData($this->data);

    $this->applicationProcess = ['id' => 22, 'funding_case_id' => 33];
    $this->fundingCase = ['id' => 33, 'funding_case_type_id' => 44];
    $this->fundingCaseType = ['id' => 44];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingApplicationProcess', 22, '00', $this->applicationProcess],
      ['FundingCase', 33, '00', $this->fundingCase],
      ['FundingCaseType', 44, '00', $this->fundingCaseType],
    ]);
  }

  public function testValid(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingApplicationProcessValidateFormEvent::getEventName(
            'RemoteFundingApplicationProcess', 'validateForm'
          ),
          static::callback(
            function (RemoteFundingApplicationProcessValidateFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->data, $event->getData());
              static::assertSame($this->applicationProcess, $event->getApplicationProcess());
              static::assertSame($this->fundingCase, $event->getFundingCase());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());

              $event->setValid(TRUE);

              return TRUE;
            }),
        ],
        [
          RemoteFundingApplicationProcessValidateFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(RemoteFundingApplicationProcessValidateFormEvent::class),
        ],
        [
          RemoteFundingApplicationProcessValidateFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingApplicationProcessValidateFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'valid' => TRUE,
      'errors' => [],
    ], $result->getArrayCopy());
  }

  public function testInvalid(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingApplicationProcessValidateFormEvent::getEventName(
            'RemoteFundingApplicationProcess', 'validateForm'
          ),
          static::callback(
            function (RemoteFundingApplicationProcessValidateFormEvent $event): bool {
              $event->addError('/foo', 'Bar');

              return TRUE;
            }),
        ],
        [
          RemoteFundingApplicationProcessValidateFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(RemoteFundingApplicationProcessValidateFormEvent::class),
        ],
        [
          RemoteFundingApplicationProcessValidateFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingApplicationProcessValidateFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'valid' => FALSE,
      'errors' => ['/foo' => ['Bar']],
    ], $result->getArrayCopy());
  }

  public function testNoValidation(): void {
    $this->expectException(\API_Exception::class);
    $this->expectExceptionMessage('Form not validated');
    $result = new Result();
    $this->action->_run($result);
  }

}
