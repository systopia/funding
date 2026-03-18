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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand;
use Civi\Funding\FundingCaseType\FallbackFundingCaseTypeServiceInterface;

// phpcs:ignore Generic.Files.LineLength.TooLong
final class FundingCasePossibleActionsGetHandler implements FundingCasePossibleActionsGetHandlerInterface, FallbackFundingCaseTypeServiceInterface {

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  public function __construct(FundingCaseActionsDeterminerInterface $actionsDeterminer) {
    $this->actionsDeterminer = $actionsDeterminer;
  }

  /**
   * @inheritDoc
   */
  public function handle(FundingCasePossibleActionsGetCommand $command): array {
    return $this->actionsDeterminer->getActions(
      $command->getFundingCaseBundle(),
      $command->getApplicationProcessStatusList(),
    );
  }

}
