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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\Generic\Result;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\Traits\CreateMockTrait;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction
 * @covers \Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent
 */
final class SubmitFormActionTest extends AbstractFormActionTest {

  use CreateMockTrait;

  private SubmitFormAction $action;

  /**
   * @var array<string, mixed>
   */
  private array $data;

  protected function setUp(): void {
    parent::setUp();
    $this->action = $this->createApi4ActionMock(
      SubmitFormAction::class,
      $this->applicationProcessBundleLoaderMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId(static::REMOTE_CONTACT_ID);
    $this->action->setApplicationProcessId($this->applicationProcessBundle->getApplicationProcess()->getId());
    $this->action->setExtraParam('contactId', static::CONTACT_ID);
    $this->data = ['foo' => 'bar'];
    $this->action->setData($this->data);
  }

  public function testShowValidation(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          SubmitApplicationFormEvent::getEventName(
            'RemoteFundingApplicationProcess', 'submitForm'
          ),
          static::callback(
            function (SubmitApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->data, $event->getData());
              static::assertSame($this->applicationProcessBundle, $event->getApplicationProcessBundle());

              $event->addError('/foo', 'Bar');

              return TRUE;
            }),
        ],
        [
          SubmitApplicationFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(SubmitApplicationFormEvent::class),
        ],
        [
          SubmitApplicationFormEvent::getEventName(),
          static::isInstanceOf(SubmitApplicationFormEvent::class),
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

  public function testCloseForm(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          SubmitApplicationFormEvent::getEventName(
            'RemoteFundingApplicationProcess', 'submitForm'
          ),
          static::callback(
            function (SubmitApplicationFormEvent $event): bool {
              $event->setAction(RemoteSubmitResponseActions::CLOSE_FORM);
              $event->setMessage('Test');
              $event->setFiles(['https://example.org/test.txt' => 'https://example.net/test,txt']);

              return TRUE;
            }),
        ],
        [
          SubmitApplicationFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(SubmitApplicationFormEvent::class),
        ],
        [
          SubmitApplicationFormEvent::getEventName(),
          static::isInstanceOf(SubmitApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'action' => 'closeForm',
      'message' => 'Test',
      'files' => ['https://example.org/test.txt' => 'https://example.net/test,txt'],
    ], $result->getArrayCopy());
  }

  public function testNoAction(): void {
    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Form not handled');

    $result = new Result();
    $this->action->_run($result);
  }

}
