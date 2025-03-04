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
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\FundingCase\Api4\ActionHandler\Traits\NewApplicationFormRemoteActionHandlerTrait;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\RemoteValidateNewApplicationFormActionHandler
 */
final class RemoteValidateNewApplicationFormActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private RemoteValidateNewApplicationFormActionHandler $actionHandler;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $newValidateHandlerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $relationCheckerMock;

  protected function setUp(): void {
    parent::setUp();
    ClockMock::register(NewApplicationFormRemoteActionHandlerTrait::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->newValidateHandlerMock = $this->createMock(ApplicationFormNewValidateHandlerInterface::class);
    $this->relationCheckerMock = $this->createMock(FundingCaseTypeProgramRelationChecker::class);
    $this->actionHandler = new RemoteValidateNewApplicationFormActionHandler(
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->newValidateHandlerMock,
      $this->relationCheckerMock
    );
  }

  public function testValidateNewApplicationForm(): void {
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

    $command = new ApplicationFormNewValidateCommand(
      $fundingProgram,
      $fundingCaseType,
      ['foo' => 'bar']
    );

    $validationResult = ApplicationFormValidationResultFactory::createValid();
    $this->newValidateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($validationResult);

    $action = $this->createApi4ActionMock(ValidateNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());
    $action->setData(['foo' => 'bar']);

    static::assertEquals([
      'valid' => TRUE,
      'errors' => new \stdClass(),
    ], $this->actionHandler->validateNewApplicationForm($action));
  }

  public function testValidateNewApplicationFormInvalid(): void {
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

    $command = new ApplicationFormNewValidateCommand(
      $fundingProgram,
      $fundingCaseType,
      ['foo' => 'bar']
    );

    $validationResult = ApplicationFormValidationResultFactory::createInvalid(['/foo' => ['error']]);
    $this->newValidateHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($validationResult);

    $action = $this->createApi4ActionMock(ValidateNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());
    $action->setData(['foo' => 'bar']);

    static::assertEquals([
      'valid' => FALSE,
      'errors' => ['/foo' => ['error']],
    ], $this->actionHandler->validateNewApplicationForm($action));
  }

  public function testValidateNewApplicationFormNoRelation(): void {
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

    $this->newValidateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(ValidateNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());
    $action->setData(['foo' => 'bar']);

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Funding program and funding case type are not related');
    $this->actionHandler->validateNewApplicationForm($action);
  }

  public function testValidateNewApplicationFormBefore(): void {
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

    $this->newValidateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(ValidateNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());
    $action->setData(['foo' => 'bar']);

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Funding program does not allow applications before 2022-06-22');
    $this->actionHandler->validateNewApplicationForm($action);
  }

  public function testValidateNewApplicationFormAfter(): void {
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

    $this->newValidateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(ValidateNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());
    $action->setData(['foo' => 'bar']);

    $this->expectException(FundingException::class);
    $this->expectExceptionMessage('Funding program does not allow applications after 2022-12-31');
    $this->actionHandler->validateNewApplicationForm($action);
  }

  public function testValidateNewApplicationFormNoPermission(): void {
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

    $this->newValidateHandlerMock->expects(static::never())->method('handle');

    $action = $this->createApi4ActionMock(ValidateNewApplicationFormAction::class);
    $action->setFundingCaseTypeId($fundingCaseType->getId());
    $action->setFundingProgramId($fundingProgram->getId());
    $action->setData(['foo' => 'bar']);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Required permission is missing');
    $this->actionHandler->validateNewApplicationForm($action);
  }

}
