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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\OptionsLoaderInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ExternalFileFactory;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\Mock\ApplicationProcess\Form\Validation\ApplicationFormValidationResultFactory;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Api4\ActionHandler\RemoteSubmitFormActionHandler
 */
final class RemoteSubmitFormActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private ApplicationProcessBundleLoader&MockObject $applicationProcessBundleLoaderMock;

  private RemoteSubmitFormActionHandler $hander;

  private OptionsLoaderInterface&MockObject $optionsLoaderMock;

  private ApplicationFormSubmitHandlerInterface&MockObject $submitHandlerMock;

  /**
   * @var array<int, FullApplicationProcessStatus>
   */
  private array $statusList;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->optionsLoaderMock = $this->createMock(OptionsLoaderInterface::class);
    $this->submitHandlerMock = $this->createMock(ApplicationFormSubmitHandlerInterface::class);
    $this->hander = new RemoteSubmitFormActionHandler(
      $this->applicationProcessBundleLoaderMock,
      $this->optionsLoaderMock,
      $this->submitHandlerMock,
    );

    $this->statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
  }

  public function testSubmitForm(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $contactId = 1;
    $action = $this->createSubmitFormAction($applicationProcessBundle, $contactId);
    $command = new ApplicationFormSubmitCommand(
      $contactId, $applicationProcessBundle, $this->statusList, $action->getData()
    );

    $validationResult = ApplicationFormValidationResultFactory::createValid(['_action' => 'save']);
    $result = ApplicationFormSubmitResult::createSuccess($validationResult);
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
    ], $this->hander->submitForm($action));
  }

  public function testOnSubmitFormInvalid(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $contactId = 1;
    $action = $this->createSubmitFormAction($applicationProcessBundle, $contactId);
    $command = new ApplicationFormSubmitCommand(
      $contactId, $applicationProcessBundle, $this->statusList, $action->getData()
    );

    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationFormValidationResultFactory::createInvalid($errorMessages, ['_action' => 'save']);
    $result = ApplicationFormSubmitResult::createError($validationResult);
    $this->submitHandlerMock->expects(static::once())->method('handle')
      ->with($command)
      ->willReturn($result);

    static::assertEquals([
      'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
      'message' => 'Validation failed',
      'errors' => $errorMessages,
    ], $this->hander->submitForm($action));
  }

  private function createSubmitFormAction(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    int $contactId
  ): SubmitFormAction {
    $this->applicationProcessBundleLoaderMock->method('get')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn($applicationProcessBundle);

    $this->applicationProcessBundleLoaderMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($this->statusList);

    $action = $this->createApi4RemoteActionMock(SubmitFormAction::class, $contactId);

    return $action
      ->setApplicationProcessId($applicationProcessBundle->getApplicationProcess()->getId())
      ->setData([]);
  }

}
