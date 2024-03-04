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

use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewFormAction;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewGetCommand;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewGetHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class GetNewFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseFormNewGetHandlerInterface $formNewGetHandler;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    FundingCaseFormNewGetHandlerInterface $formNewGetHandler,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager
  ) {
    $this->formNewGetHandler = $formNewGetHandler;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @phpstan-return array{
   *   jsonSchema: array<int|string, mixed>,
   *   uiSchema: array<int|string, mixed>,
   *   data: array<string, mixed>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function getNewForm(GetNewFormAction $action): array {
    $fundingProgram = $this->fundingProgramManager->get($action->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf('Funding program with id "%d" not found', $action->getFundingProgramId()));
    $fundingCaseType = $this->fundingCaseTypeManager->get($action->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType, sprintf(
      'Funding case type with id "%d" not found',
      $action->getFundingCaseTypeId(),
    ));

    $form = $this->formNewGetHandler->handle(new FundingCaseFormNewGetCommand(
      $action->getResolvedContactId(),
      $fundingProgram,
      $fundingCaseType,
    ));

    return [
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
      'data' => $form->getData(),
    ];
  }

}
