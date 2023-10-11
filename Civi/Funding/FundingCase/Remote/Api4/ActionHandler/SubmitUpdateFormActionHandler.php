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

namespace Civi\Funding\FundingCase\Remote\Api4\ActionHandler;

use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitUpdateFormAction;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;
use CRM_Funding_ExtensionUtil as E;

final class SubmitUpdateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private FundingCaseFormUpdateSubmitHandlerInterface $submitHandler;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    FundingCaseFormUpdateSubmitHandlerInterface $submitHandler
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->submitHandler = $submitHandler;
  }

  /**
   * @phpstan-return array{
   *    action: RemoteSubmitResponseActions::*,
   *    message: string,
   *    errors?: array<string, non-empty-array<string>>,
   *  }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitUpdateForm(SubmitUpdateFormAction $action): array {
    $fundingCase = $this->fundingCaseManager->get($action->getFundingCaseId());
    Assert::notNull($fundingCase, sprintf('Funding case with ID %d not found', $action->getFundingCaseId()));
    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull(
      $fundingProgram,
      sprintf('Funding program with ID %d not found', $fundingCase->getFundingProgramId())
    );
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType, sprintf(
      'Funding case type with ID %d not found',
      $fundingCase->getFundingCaseTypeId(),
    ));

    $submitResult = $this->submitHandler->handle(new FundingCaseFormUpdateSubmitCommand(
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

    if (FundingCaseActions::DELETE === $submitResult->getValidatedData()->getAction()) {
      return [
        'action' => RemoteSubmitResponseActions::CLOSE_FORM,
        'message' => E::ts('Deleted'),
      ];
    }

    return [
      'action' => RemoteSubmitResponseActions::RELOAD_FORM,
      'message' => E::ts('Saved'),
    ];
  }

}
