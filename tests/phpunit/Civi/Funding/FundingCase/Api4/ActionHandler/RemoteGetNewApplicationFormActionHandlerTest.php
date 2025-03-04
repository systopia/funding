<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\FundingCase\Api4\ActionHandler\Traits\NewApplicationFormRemoteActionHandlerTrait;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Mock\Form\ApplicationFormMock;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\RemoteGetNewApplicationFormActionHandler
 */
final class RemoteGetNewApplicationFormActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private RemoteGetNewApplicationFormActionHandler $actionHandler;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newCreateHandlerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $relationCheckerMock;

  protected function setUp(): void {
    parent::setUp();
    ClockMock::register(NewApplicationFormRemoteActionHandlerTrait::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->newCreateHandlerMock = $this->createMock(ApplicationFormNewCreateHandlerInterface::class);
    $this->relationCheckerMock = $this->createMock(FundingCaseTypeProgramRelationChecker::class);
    $this->actionHandler = new RemoteGetNewApplicationFormActionHandler(
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->newCreateHandlerMock,
      $this->relationCheckerMock
    );
  }

  public function testGetNewApplicationForm(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCaseType->getId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'permissions' => ['application_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingProgram->getId())
      ->willReturn($fundingProgram);

    ClockMock::withClockMock($fundingProgram->getRequestsStartDate()->getTimestamp());

    $this->relationCheckerMock->expects(self::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with($fundingCaseType->getId(), $fundingProgram->getId())
      ->willReturn(TRUE);

    $command = new ApplicationFormNewCreateCommand(
      $fundingCaseType,
      $fundingProgram
    );

    $form = new ApplicationFormMock(NULL, NULL, ['foo' => 'bar']);
    $this->newCreateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($form);

    $action = $this->createApi4ActionMock(GetNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());

    static::assertEquals([
      'data' => $form->getData(),
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
    ], $this->actionHandler->getNewApplicationForm($action));
  }

  public function testGetNewApplicationFormNoRelation(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCaseType->getId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'permissions' => ['application_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingProgram->getId())
      ->willReturn($fundingProgram);

    ClockMock::withClockMock($fundingProgram->getRequestsStartDate()->getTimestamp() - 86400);

    $this->relationCheckerMock->expects(self::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with($fundingCaseType->getId(), $fundingProgram->getId())
      ->willReturn(FALSE);

    $this->newCreateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(GetNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Funding program and funding case type are not related');
    $this->actionHandler->getNewApplicationForm($action);
  }

  public function testGetNewApplicationFormBefore(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCaseType->getId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'permissions' => ['application_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingProgram->getId())
      ->willReturn($fundingProgram);

    ClockMock::withClockMock($fundingProgram->getRequestsStartDate()->getTimestamp() - 86400);

    $this->relationCheckerMock->expects(self::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with($fundingCaseType->getId(), $fundingProgram->getId())
      ->willReturn(TRUE);

    $this->newCreateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(GetNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Funding program does not allow applications before 2022-06-22');
    $this->actionHandler->getNewApplicationForm($action);
  }

  public function testGetNewApplicationFormAfter(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCaseType->getId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'permissions' => ['application_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingProgram->getId())
      ->willReturn($fundingProgram);

    ClockMock::withClockMock($fundingProgram->getRequestsEndDate()->getTimestamp() + 86400);

    $this->relationCheckerMock->expects(self::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with($fundingCaseType->getId(), $fundingProgram->getId())
      ->willReturn(TRUE);

    $this->newCreateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(GetNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Funding program does not allow applications after 2022-12-31');
    $this->actionHandler->getNewApplicationForm($action);
  }

  public function testGetNewApplicationFormNoPermission(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($fundingCaseType->getId())
      ->willReturn($fundingCaseType);

    $fundingProgram = FundingProgramFactory::createFundingProgram([
      'permissions' => ['application_no_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')
      ->with($fundingProgram->getId())
      ->willReturn($fundingProgram);

    ClockMock::withClockMock($fundingProgram->getRequestsStartDate()->getTimestamp());

    $this->relationCheckerMock->expects(self::once())->method('areFundingCaseTypeAndProgramRelated')
      ->with($fundingCaseType->getId(), $fundingProgram->getId())
      ->willReturn(TRUE);

    $this->newCreateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(GetNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Required permission is missing');
    $this->actionHandler->getNewApplicationForm($action);
  }

}
