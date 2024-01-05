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

use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewFormAction;
use Civi\Funding\FundingCase\Command\FundingCaseFormNewValidateCommand;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewValidateHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class ValidateNewFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseFormNewValidateHandlerInterface $validateHandler;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    FundingCaseFormNewValidateHandlerInterface $validateHandler,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager
  ) {
    $this->validateHandler = $validateHandler;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @phpstan-return array{
   *   valid: bool,
   *   errors: array<string, non-empty-list<string>>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function validateNewForm(ValidateNewFormAction $action): array {
    $fundingProgram = $this->fundingProgramManager->get($action->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf('Funding program with id "%d" not found', $action->getFundingProgramId()));
    $fundingCaseType = $this->fundingCaseTypeManager->get($action->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType, sprintf(
      'Funding case type with id "%d" not found',
      $action->getFundingCaseTypeId(),
    ));

    $validateResult = $this->validateHandler->handle(new FundingCaseFormNewValidateCommand(
      $action->getResolvedContactId(),
      $fundingProgram,
      $fundingCaseType,
      $action->getData(),
    ));

    return [
      'valid' => $validateResult->isValid(),
      'errors' => $validateResult->getErrorMessages(),
    ];
  }

}
