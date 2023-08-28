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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Generic\Result;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\Traits\CreateMockTrait;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction
 * @covers \Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent
 */
final class SubmitNewApplicationFormActionTest extends AbstractNewApplicationFormActionTest {

  use CreateMockTrait;

  private SubmitNewApplicationFormAction $action;

  /**
   * @var array<string, mixed>
   */
  private array $data;

  protected function setUp(): void {
    parent::setUp();
    $this->action = $this->createApi4ActionMock(
      SubmitNewApplicationFormAction::class,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->eventDispatcherMock,
      $this->relationCheckerMock,
    );

    $this->data = ['foo' => 'bar'];
    $this->action
      ->setRemoteContactId('00')
      ->setExtraParam('contactId', 11)
      ->setFundingProgramId(33)
      ->setFundingCaseTypeId(22)
      ->setData($this->data);
  }

  public function testShowValidation(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          SubmitNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'submitNewApplicationForm'
          ),
          static::callback(
            function (SubmitNewApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->data, $event->getData());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->addError('/foo', 'Bar');

              return TRUE;
            }),
        ],
        [
          SubmitNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(SubmitNewApplicationFormEvent::class),
        ],
        [
          SubmitNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(SubmitNewApplicationFormEvent::class),
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
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          SubmitNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'submitNewApplicationForm'
          ),
          static::callback(
            function (SubmitNewApplicationFormEvent $event): bool {
              $event->setAction(RemoteSubmitResponseActions::CLOSE_FORM);
              $event->setMessage('Test');
              $event->setFiles(['https://example.org/test.txt' => 'https://example.net/test,txt']);

              return TRUE;
            }),
        ],
        [
          SubmitNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(SubmitNewApplicationFormEvent::class),
        ],
        [
          SubmitNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(SubmitNewApplicationFormEvent::class),
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
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Form not handled');

    $result = new Result();
    $this->action->_run($result);
  }

  public function testFundingCaseTypeAndProgramNotRelated(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(FALSE);

    static::expectExceptionObject(new FundingException(
      'Funding program and funding case type are not related',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

  public function testPermissionMissing(): void {
    $this->fundingProgram->setValues(
      ['permissions' => ['some_permission']] + $this->fundingProgram->toArray()
    );

    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    static::expectExceptionObject(new UnauthorizedException('Required permission is missing'));

    $result = new Result();
    $this->action->_run($result);
  }

  public function testApplicationTooEarly(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->fundingProgram->setRequestsStartDate(new \DateTime('1970-01-03'));

    static::expectExceptionObject(new FundingException(
      'Funding program does not allow applications before 1970-01-03',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

  public function testApplicationTooLate(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->fundingProgram->setRequestsEndDate(new \DateTime('1970-01-01'));

    static::expectExceptionObject(new FundingException(
      'Funding program does not allow applications after 1970-01-01',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
