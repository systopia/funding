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
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\RemoteTools\Form\JsonForms\JsonFormsElement;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction
 * @covers \Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent
 * @covers \Civi\Funding\Event\Remote\AbstractFundingGetFormEvent
 */
final class GetNewApplicationFormActionTest extends AbstractNewApplicationFormActionTest {

  private GetNewApplicationFormAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->action = new GetNewApplicationFormAction(
      $this->remoteFundingEntityManagerMock,
      $this->eventDispatcherMock,
      $this->relationCheckerMock,
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setFundingCaseTypeId(22);
    $this->action->setFundingProgramId(33);
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
              static::assertSame($this->fundingCaseTypeValues, $event->getFundingCaseType()->toArray());
              static::assertSame($this->fundingProgramValues, $event->getFundingProgram()->toArray());

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
      'Invalid funding program ID or funding case type ID',
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

  public function testPermissionMissing(): void {
    $this->fundingProgramValues['permissions'] = ['some_permission'];

    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    static::expectExceptionObject(new UnauthorizedException('Required permission is missing'));

    $result = new Result();
    $this->action->_run($result);
  }

  public function testApplicationTooEarly(): void {
    $this->relationCheckerMock->expects(static::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with(22, 33)->willReturn(TRUE);

    $this->fundingProgramValues['requests_start_date'] = '1970-01-03';

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

    $this->fundingProgramValues['requests_end_date'] = '1970-01-01';

    static::expectExceptionObject(new \API_Exception(
      'Funding program does not allow applications after 1970-01-01',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
