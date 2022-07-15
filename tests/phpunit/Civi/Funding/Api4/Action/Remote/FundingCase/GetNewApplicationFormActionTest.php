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

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Form\JsonForms\JsonFormsElement;
use Civi\Funding\Form\JsonSchema\JsonSchema;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction
 * @covers \Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingGetFormEvent
 */
final class GetNewApplicationFormActionTest extends TestCase {

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
    $this->action = new GetNewApplicationFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock,
      $this->relationCheckerMock,
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setFundingCaseTypeId(22);
    $this->action->setFundingProgramId(33);

    $this->fundingCaseType = ['id' => 22];
    $this->fundingProgram = ['id' => 33, 'requests_start_date' => NULL, 'requests_end_date' => NULL];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingCaseType', 22, '00', 11, &$this->fundingCaseType],
      ['FundingProgram', 33, '00', 11, &$this->fundingProgram],
    ]);
  }

  public function testRun(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $jsonSchema = new JsonSchema(['foo' => 'test']);
    $uiSchema = new JsonFormsElement('Test');
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          GetNewApplicationFormEvent::getEventName('RemoteFundingCase', 'getNewApplicationForm'),
          static::callback(
            function (GetNewApplicationFormEvent $event) use ($jsonSchema, $uiSchema): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->setJsonSchema($jsonSchema);
              $event->setUiSchema($uiSchema);
              $event->setData(['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          GetNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(GetNewApplicationFormEvent::class),
        ],
        [
          GetNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(GetNewApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'jsonSchema' => $jsonSchema,
      'uiSchema' => $uiSchema,
      'data' => ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testNoEventListener(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    static::expectExceptionObject(new \API_Exception(
      'Invalid fundingProgramId or fundingCaseTypeId',
      'invalid_parameters'
    ));

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
