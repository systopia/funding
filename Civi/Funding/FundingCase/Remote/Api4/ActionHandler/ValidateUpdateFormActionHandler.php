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

use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateUpdateFormAction;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class ValidateUpdateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private FundingCaseFormUpdateValidateHandlerInterface $validateHandler;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    FundingCaseFormUpdateValidateHandlerInterface $validateHandler
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @phpstan-return array{
   *   valid: bool,
   *   errors: array<string, non-empty-array<string>>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function validateUpdateForm(ValidateUpdateFormAction $action): array {
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

    $validateResult = $this->validateHandler->handle(new FundingCaseFormUpdateValidateCommand(
      $fundingProgram, $fundingCaseType, $fundingCase, $action->getData(),
    ));

    return [
      'valid' => $validateResult->isValid(),
      'errors' => $validateResult->getErrorMessages(),
    ];
  }

}
