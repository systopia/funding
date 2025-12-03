<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction;
use Civi\Funding\Api4\OptionsLoaderInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Api4\ActionHandler\RemoteSubmitNewApplicationFormActionHandler
 */
final class RemoteSubmitNewApplicationFormActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private bool $areFundingCaseTypeAndProgramRelated = TRUE;

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingCaseTypeManager&MockObject $fundingCaseTypeManagerMock;

  private FundingProgramEntity $fundingProgram;

  private FundingProgramManager&MockObject $fundingProgramManagerMock;

  private RemoteSubmitNewApplicationFormActionHandler $handler;

  private OptionsLoaderInterface&MockObject $optionsLoaderMock;

  private FundingCaseTypeProgramRelationChecker&MockObject $relationCheckerMock;

  private ApplicationFormNewSubmitHandlerInterface&MockObject $submitHandlerMock;

  protected function setUp(): void {
    parent::setUp();

    putenv('TIME_FUNC=frozen');
    \CRM_Utils_Time::setTime('2000-01-01 00:00:00');

    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->relationCheckerMock = $this->createMock(FundingCaseTypeProgramRelationChecker::class);
    $this->optionsLoaderMock = $this->createMock(OptionsLoaderInterface::class);
    $this->submitHandlerMock = $this->createMock(ApplicationFormNewSubmitHandlerInterface::class);

    $this->handler = new RemoteSubmitNewApplicationFormActionHandler(
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
      $this->relationCheckerMock,
      $this->optionsLoaderMock,
      $this->submitHandlerMock
    );

    $this->fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')
      ->with($this->fundingCaseType->getId())
      ->willReturn($this->fundingCaseType);
    $this->fundingProgram = FundingProgramFactory::createFundingProgram([
      'requests_start_date' => \CRM_Utils_Time::date('Y-m-d'),
      'requests_end_date' => \CRM_Utils_Time::date('Y-m-d'),
      'permissions' => ['application_create'],
    ]);
    $this->fundingProgramManagerMock->method('get')
      ->with($this->fundingProgram->getId())
      ->willReturn($this->fundingProgram);

    $this->relationCheckerMock->method('areFundingCaseTypeAndProgramRelated')
      ->with($this->fundingCaseType->getId(), $this->fundingProgram->getId())
      ->willReturnCallback(fn () => $this->areFundingCaseTypeAndProgramRelated);
  }

  public function testSuccess(): void {
    $this->areFundingCaseTypeAndProgramRelated = TRUE;
    $contactId = 1;
    $action = $this->createSubmitNewFormAction($contactId);
    $command = new ApplicationFormNewSubmitCommand(
      $contactId,
      $this->fundingCaseType,
      $this->fundingProgram,
      $action->getData()
    );

    $validationResult = ApplicationFormValidationResultFactory::createValid();
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $result = ApplicationFormNewSubmitResult::createSuccess(
      $validationResult,
      $applicationProcessBundle,
    );
    $result->setFiles([
      'https://example.org/test.txt' => ExternalFileFactory::create(
        ['uri' => 'https://example.net/test.txt'],
      ),
    ]);
    $this->submitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    $this->optionsLoaderMock->method('getOptionLabel')
      ->with(
        FundingApplicationProcess::getEntityName(),
        'status',
        $applicationProcessBundle->getApplicationProcess()->getStatus()
      )->willReturn('New Status');

    static::assertEquals([
      'action' => RemoteSubmitResponseActions::CLOSE_FORM,
      'message' => 'Saved (Status: New Status)',
      'files' => ['https://example.org/test.txt' => 'https://example.net/test.txt'],
    ], $this->handler->submitNewApplicationForm($action));
  }

  public function testApplicationTooEarly(): void {
    $contactId = 1;
    $action = $this->createSubmitNewFormAction($contactId);

    \CRM_Utils_Time::setTime(date('YmdHis', \CRM_Utils_Time::time() - 1));

    static::expectExceptionObject(new FundingException(
      'Funding program does not allow applications before 2000-01-01',
      'invalid_parameters'
    ));
    $this->handler->submitNewApplicationForm($action);
  }

  public function testApplicationTooLate(): void {
    $contactId = 1;
    $action = $this->createSubmitNewFormAction($contactId);

    \CRM_Utils_Time::setTime(date('YmdHis', \CRM_Utils_Time::time() + 24 * 60 * 60));

    static::expectExceptionObject(new FundingException(
      'Funding program does not allow applications after 2000-01-01',
      'invalid_parameters'
    ));
    $this->handler->submitNewApplicationForm($action);
  }

  public function testFundingCaseTypeAndProgramNotRelated(): void {
    $this->areFundingCaseTypeAndProgramRelated = FALSE;
    $contactId = 1;
    $action = $this->createSubmitNewFormAction($contactId);

    static::expectExceptionObject(new FundingException(
      'Funding program and funding case type are not related',
      'invalid_parameters'
    ));

    $this->handler->submitNewApplicationForm($action);
  }

  public function testPermissionMissing(): void {
    $this->fundingProgram->setValues(
      ['permissions' => ['some_permission']] + $this->fundingProgram->toArray()
    );
    $contactId = 1;
    $action = $this->createSubmitNewFormAction($contactId);

    static::expectExceptionObject(new UnauthorizedException('Required permission is missing'));

    $this->handler->submitNewApplicationForm($action);
  }

  private function createSubmitNewFormAction(int $contactId): SubmitNewApplicationFormAction {

    return $this->createApi4RemoteActionMock(SubmitNewApplicationFormAction::class, $contactId)
      ->setFundingCaseTypeId($this->fundingCaseType->getId())
      ->setFundingProgramId($this->fundingProgram->getId())
      ->setData(['foo' => 'bar']);
  }

}
