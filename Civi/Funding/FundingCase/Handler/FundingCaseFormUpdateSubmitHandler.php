<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitCommand;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitResult;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\Helper\ApplicationAllowedActionApplier;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;

final class FundingCaseFormUpdateSubmitHandler implements FundingCaseFormUpdateSubmitHandlerInterface {

  private ApplicationAllowedActionApplier $applicationAllowedActionApplier;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseStatusDeterminerInterface $statusDeterminer;

  private FundingCaseFormUpdateValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationAllowedActionApplier $applicationAllowedActionApplier,
    FundingCaseManager $fundingCaseManager,
    FundingCaseStatusDeterminerInterface $statusDeterminer,
    FundingCaseFormUpdateValidateHandlerInterface $validateHandler
  ) {
    $this->applicationAllowedActionApplier = $applicationAllowedActionApplier;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
    $this->validateHandler = $validateHandler;
  }

  public function handle(FundingCaseFormUpdateSubmitCommand $command): FundingCaseFormUpdateSubmitResult {
    $validationResult = $this->validateHandler->handle(new FundingCaseFormUpdateValidateCommand(
      $command->getFundingProgram(), $command->getFundingCaseType(), $command->getFundingCase(), $command->getData(),
    ));

    if (!$validationResult->isValid()) {
      return FundingCaseFormUpdateSubmitResult::createError($validationResult, $command->getFundingCase());
    }

    $action = $validationResult->getValidatedData()->getAction();
    $fundingCase = $command->getFundingCase();
    $this->applicationAllowedActionApplier->applyAllowedActionsByFundingCase(
      $command->getContactId(),
      $fundingCase,
      $action
    );

    if (FundingCaseActions::DELETE === $action) {
      $this->fundingCaseManager->delete($fundingCase);
    }
    else {
      $fundingCase->setStatus($this->statusDeterminer->getStatus($fundingCase->getStatus(), $action));
      $this->fundingCaseManager->update($fundingCase);
    }

    return FundingCaseFormUpdateSubmitResult::createSuccess(
      $validationResult,
      $command->getFundingCase(),
    );
  }

}
