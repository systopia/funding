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

use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewFormAction;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewSubmitCommand;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewSubmitHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;
use CRM_Funding_ExtensionUtil as E;

final class SubmitNewFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseFormNewSubmitHandlerInterface $submitHandler;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    FundingCaseFormNewSubmitHandlerInterface $submitHandler,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager
  ) {
    $this->submitHandler = $submitHandler;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @phpstan-return array{
   *    action: string,
   *    message: string,
   *    errors?: array<string, non-empty-array<string>>,
   *    entity_name?: string,
   *    entity_id?: int,
   *  }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitNewForm(SubmitNewFormAction $action): array {
    $fundingProgram = $this->fundingProgramManager->get($action->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf('Funding program with id "%d" not found', $action->getFundingProgramId()));
    $fundingCaseType = $this->fundingCaseTypeManager->get($action->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType, sprintf(
      'Funding case type with id "%d" not found',
      $action->getFundingCaseTypeId(),
    ));

    $submitResult = $this->submitHandler->handle(new FundingCaseFormNewSubmitCommand(
      $action->getResolvedContactId(),
      $fundingProgram,
      $fundingCaseType,
      $action->getData(),
    ));

    if (!$submitResult->isSuccess()) {
      return [
        'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
        'message' => E::ts('Validation failed'),
        'errors' => $submitResult->getValidationResult()->getErrorMessages(),
      ];
    }

    Assert::notNull($submitResult->getFundingCase());
    return [
      'action' => RemoteSubmitResponseActions::LOAD_ENTITY,
      'message' => E::ts('Saved'),
      'entity_type' => 'FundingCase',
      'entity_id' => $submitResult->getFundingCase()->getId(),
    ];
  }

}
