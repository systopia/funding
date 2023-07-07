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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Traits\CreateMockTrait;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction
 * @covers \Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingValidateFormEvent
 */
final class ValidateNewApplicationFormActionTest extends AbstractNewApplicationFormActionTest {

  use CreateMockTrait;

  private ValidateNewApplicationFormAction $action;

  /**
   * @var array<string, mixed>
   */
  private array $data;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::register(NewApplicationFormActionTrait::class);
    ClockMock::withClockMock(strtotime('1970-01-02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->action = $this->createApi4ActionMock(
      ValidateNewApplicationFormAction::class,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->eventDispatcherMock,
      $this->relationCheckerMock,
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->data = [
      'fundingCaseTypeId' => 22,
      'fundingProgramId' => 33,
    ];
    $this->action->setData($this->data);
  }

  public function testValid(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          ValidateNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'validateNewApplicationForm'
          ),
          static::callback(
            function (ValidateNewApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->data, $event->getData());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->setValid(TRUE);

              return TRUE;
            }),
        ],
        [
          ValidateNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(ValidateNewApplicationFormEvent::class),
        ],
        [
          ValidateNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(ValidateNewApplicationFormEvent::class),
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
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          ValidateNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'validateNewApplicationForm'
          ),
          static::callback(
            function (ValidateNewApplicationFormEvent $event): bool {
              $event->addError('/foo', 'Bar');

              return TRUE;
            }),
        ],
        [
          ValidateNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(ValidateNewApplicationFormEvent::class),
        ],
        [
          ValidateNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(ValidateNewApplicationFormEvent::class),
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
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Form not validated');
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
