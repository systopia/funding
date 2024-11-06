<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitAddFormAction;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddSubmitHandlerInterface;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class SubmitAddFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private ApplicationFormAddSubmitHandlerInterface $submitHandler;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private OptionsLoaderInterface $optionsLoader;

  public function __construct(
    ApplicationFormAddSubmitHandlerInterface $submitHandler,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    OptionsLoaderInterface $optionsLoader
  ) {
    $this->submitHandler = $submitHandler;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->optionsLoader = $optionsLoader;
  }

  /**
   * @phpstan-return array{
   *   action: string,
   *   message: string,
   *   errors?: array<string, non-empty-list<string>>,
   *   files?: array<string, string>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitAddForm(SubmitAddFormAction $action): array {
    $fundingCase = $this->fundingCaseManager->get($action->getFundingCaseId());
    Assert::notNull($fundingCase, sprintf('Funding case with id "%d" not found', $action->getFundingCaseId()));

    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram);
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);

    $submitResult = $this->submitHandler->handle(new ApplicationFormAddSubmitCommand(
      $action->getResolvedContactId(),
      $fundingProgram,
      $fundingCaseType,
      $fundingCase,
      $action->getData(),
    ));

    if (!$submitResult->isSuccess()) {
      return [
        'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
        'message' => E::ts('Validation failed'),
        'errors' => $submitResult->getValidationResult()->getErrorMessages(),
      ];
    }

    Assert::notNull($submitResult->getApplicationProcessBundle());
    $result = [
      'action' => RemoteSubmitResponseActions::CLOSE_FORM,
      'message' => E::ts('Saved (Status: %1)', [
        1 => $this->optionsLoader->getOptionLabel(
          FundingApplicationProcess::getEntityName(),
          'status',
          $submitResult->getApplicationProcessBundle()->getApplicationProcess()->getStatus()
        ),
      ]),
      'files' => array_map(
        fn (ExternalFileEntity $file) => $file->getUri(),
        $submitResult->getFiles(),
      ),
    ];

    if ($this->isShouldAddNext($submitResult->getValidatedData()->getAction())) {
      $result['action'] = RemoteSubmitResponseActions::RELOAD_FORM;
    }
    elseif ($this->isShouldAddNextAsCopy($submitResult->getValidatedData()->getAction())) {
      $result['action'] = RemoteSubmitResponseActions::RELOAD_FORM;
      $result['copyDataFromId'] = $submitResult->getApplicationProcessBundle()->getApplicationProcess()->getId();
    }

    return $result;
  }

  private function isShouldAddNext(string $action): bool {
    return str_ends_with($action, '&new');
  }

  private function isShouldAddNextAsCopy(string $action): bool {
    return str_ends_with($action, '&copy');
  }

}
