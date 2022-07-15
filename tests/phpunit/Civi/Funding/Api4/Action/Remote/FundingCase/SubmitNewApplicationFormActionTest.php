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

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\FundingForm;
use Civi\Funding\Form\JsonForms\JsonFormsElement;
use Civi\Funding\Form\JsonSchema\JsonSchema;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction
 * @covers \Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent
 */
final class SubmitNewApplicationFormActionTest extends TestCase {

  private SubmitNewApplicationFormAction $action;


  /**
   * @var array<string, mixed>
   */
  private array $data;

  /**
   * @var \Civi\Core\CiviEventDispatcher&\PHPUnit\Framework\MockObject\MockObject
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

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker
   */
  private MockObject $relationCheckerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(NewApplicationFormActionTrait::class);
    ClockMock::withClockMock(strtotime('1970-01-02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->relationCheckerMock = $this->createMock(FundingCaseTypeProgramRelationChecker::class);
    $this->action = new SubmitNewApplicationFormAction(
      $remoteFundingEntityManagerMock,
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

    $this->fundingCaseType = ['id' => 22];
    $this->fundingProgram = ['id' => 33, 'requests_start_date' => NULL, 'requests_end_date' => NULL];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingCaseType', 22, '00', 11, &$this->fundingCaseType],
      ['FundingProgram', 33, '00', 11, &$this->fundingProgram],
    ]);
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

  public function testShowForm(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $jsonSchema = new JsonSchema(['foo' => 'test']);
    $uiSchema = new JsonFormsElement('Test');
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          SubmitNewApplicationFormEvent::getEventName(
            'RemoteFundingCase', 'submitNewApplicationForm'
          ),
          static::callback(
            function (SubmitNewApplicationFormEvent $event) use ($jsonSchema, $uiSchema): bool {
              $data = ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'];
              $event->setForm(new FundingForm($jsonSchema, $uiSchema, $data));
              $event->setMessage('Test');

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
      'action' => 'showForm',
      'message' => 'Test',
      'jsonSchema' => $jsonSchema,
      'uiSchema' => $uiSchema,
      'data' => ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'],
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
              $event->setAction(SubmitNewApplicationFormEvent::ACTION_CLOSE_FORM);
              $event->setMessage('Test');

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
    ], $result->getArrayCopy());
  }

  public function testNoAction(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->expectException(\API_Exception::class);
    $this->expectExceptionMessage('Form not handled');

    $result = new Result();
    $this->action->_run($result);
  }

  public function testFundingCaseTypeAndProgramNotRelated(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(FALSE);

    static::expectExceptionObject(new \API_Exception(
      'Funding program and funding case type are not related',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

  public function testApplicationTooEarly(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->fundingProgram['requests_start_date'] = '1970-01-03';

    static::expectExceptionObject(new \API_Exception(
      'Funding program does not allow applications before 1970-01-03',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

  public function testApplicationTooLate(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->fundingProgram['requests_end_date'] = '1970-01-01';

    static::expectExceptionObject(new \API_Exception(
      'Funding program does not allow applications after 1970-01-01',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
