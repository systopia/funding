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
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Traits\CreateMockTrait;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction
 * @covers \Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingValidateFormEvent
 */
final class ValidateFormActionTest extends AbstractFormActionTest {

  use CreateMockTrait;

  private ValidateFormAction $action;

  /**
   * @var array<string, mixed>
   */
  private array $data;

  protected function setUp(): void {
    parent::setUp();
    $this->action = $this->createApi4ActionMock(
      ValidateFormAction::class,
      $this->applicationProcessBundleLoaderMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId(static::REMOTE_CONTACT_ID);
    $this->action->setApplicationProcessId($this->applicationProcessBundle->getApplicationProcess()->getId());
    $this->action->setExtraParam('contactId', static::CONTACT_ID);
    $this->data = ['foo' => 'bar'];
    $this->action->setData($this->data);
  }

  public function testValid(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          ValidateApplicationFormEvent::getEventName(
            'RemoteFundingApplicationProcess', 'validateForm'
          ),
          static::callback(
            function (ValidateApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->data, $event->getData());
              static::assertSame($this->applicationProcessBundle, $event->getApplicationProcessBundle());

              $event->setValid(TRUE);

              return TRUE;
            }),
        ],
        [
          ValidateApplicationFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(ValidateApplicationFormEvent::class),
        ],
        [
          ValidateApplicationFormEvent::getEventName(),
          static::isInstanceOf(ValidateApplicationFormEvent::class),
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
          ValidateApplicationFormEvent::getEventName(
            'RemoteFundingApplicationProcess', 'validateForm'
          ),
          static::callback(
            function (ValidateApplicationFormEvent $event): bool {
              $event->addError('/foo', 'Bar');

              return TRUE;
            }),
        ],
        [
          ValidateApplicationFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(ValidateApplicationFormEvent::class),
        ],
        [
          ValidateApplicationFormEvent::getEventName(),
          static::isInstanceOf(ValidateApplicationFormEvent::class),
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
    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Form not validated');
    $result = new Result();
    $this->action->_run($result);
  }

}
