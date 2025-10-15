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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class RemoteSubmitNewApplicationFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private FundingCaseTypeProgramRelationChecker $relationChecker;

  private OptionsLoaderInterface $optionsLoader;

  private ApplicationFormNewSubmitHandlerInterface $submitHandler;

  public function __construct(
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    FundingCaseTypeProgramRelationChecker $relationChecker,
    OptionsLoaderInterface $optionsLoader,
    ApplicationFormNewSubmitHandlerInterface $submitHandler
  ) {
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->relationChecker = $relationChecker;
    $this->optionsLoader = $optionsLoader;
    $this->submitHandler = $submitHandler;
  }

  /**
   * @return array{
   *   action: string,
   *   message: string,
   *   files?: array<string, string>,
   *   errors?: array<string, non-empty-list<string>>,
   *   }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitNewApplicationForm(SubmitNewApplicationFormAction $action): array {
    $fundingCaseType = $this->fundingCaseTypeManager->get($action->getFundingCaseTypeId());
    Assert::notNull(
      $fundingCaseType,
      E::ts('Funding case type with ID "%1" not found', [1 => $action->getFundingCaseTypeId()])
    );

    $fundingProgram = $this->fundingProgramManager->get($action->getFundingProgramId());
    Assert::notNull(
      $fundingProgram,
      E::ts('Funding program with ID "%1" not found', [1 => $action->getFundingProgramId()])
    );

    $this->assertFundingCaseTypeAndProgramRelated($action->getFundingCaseTypeId(), $action->getFundingProgramId());
    $this->assertFundingProgramDates($fundingProgram);
    $this->assertCreateApplicationPermission($fundingProgram);

    $command = new ApplicationFormNewSubmitCommand(
      $action->getResolvedContactId(),
      $fundingCaseType,
      $fundingProgram,
      $action->getData()
    );

    $result = $this->submitHandler->handle($command);
    if ($result->isSuccess()) {
      Assert::notNull($result->getApplicationProcessBundle());

      return [
        'action' => RemoteSubmitResponseActions::CLOSE_FORM,
        'message' => $this->createSuccessMessage($result->getApplicationProcessBundle()->getApplicationProcess()),
        'files' => $this->convertFiles($result->getFiles()),
      ];
    }
    return [
      'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
      'message' => E::ts('Validation failed'),
      'errors' => $result->getValidationResult()->getErrorMessages(),
    ];

  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function assertCreateApplicationPermission(FundingProgramEntity $fundingProgram): void {
    if (!in_array('application_create', $fundingProgram->getPermissions(), TRUE)) {
      throw new UnauthorizedException(E::ts('Required permission is missing'));
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function assertFundingCaseTypeAndProgramRelated(int $fundingCaseTypeId, int $fundingProgramId): void {
    if (!$this->relationChecker->areFundingCaseTypeAndProgramRelated($fundingCaseTypeId, $fundingProgramId)) {
      throw new FundingException(E::ts('Funding program and funding case type are not related'), 'invalid_arguments');
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function assertFundingProgramDates(FundingProgramEntity $fundingProgram): void {
    if (new \DateTime(\CRM_Utils_Time::date('Y-m-d')) < $fundingProgram->getRequestsStartDate()) {
      throw new FundingException(E::ts(
        'Funding program does not allow applications before %1',
        [1 => $fundingProgram->getRequestsStartDate()->format(E::ts('Y-m-d'))]
      ), 'invalid_arguments');
    }

    if (new \DateTime(\CRM_Utils_Time::date('Y-m-d')) > $fundingProgram->getRequestsEndDate()) {
      throw new FundingException(E::ts(
        'Funding program does not allow applications after %1',
        [1 => $fundingProgram->getRequestsEndDate()->format(E::ts('Y-m-d'))]
      ), 'invalid_arguments');
    }
  }

  /**
   * @param array<string, \Civi\Funding\Entity\ExternalFileEntity> $files
   *
   * @return array<string, string>
   */
  private function convertFiles(array $files): array {
    return array_map(
      fn (ExternalFileEntity $file) => $file->getUri(),
      $files,
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createSuccessMessage(ApplicationProcessEntity $applicationProcess): string {
    return E::ts('Saved (Status: %1)', [
      1 => $this->optionsLoader->getOptionLabel(
        FundingApplicationProcess::getEntityName(),
        'status',
        $applicationProcess->getStatus()
      ),
    ]);
  }

}
