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

use Civi\Funding\Api4\Action\Remote\FundingCase\GetUpdateFormAction;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateGetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class GetUpdateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseFormUpdateGetHandlerInterface $formUpdateGetHandler;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    FundingCaseFormUpdateGetHandlerInterface $formUpdateGetHandler,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager
  ) {
    $this->formUpdateGetHandler = $formUpdateGetHandler;
    $this->fundingCaseManager = $fundingCaseManager;
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
  public function getUpdateForm(GetUpdateFormAction $action): array {
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

    $form = $this->formUpdateGetHandler->handle(new FundingCaseFormUpdateGetCommand(
      $action->getResolvedContactId(),
      $fundingProgram,
      $fundingCaseType,
      $fundingCase,
    ));

    return [
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
      'data' => $form->getData(),
    ];
  }

}
