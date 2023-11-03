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

namespace Civi\Funding\FundingProgram\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\FundingProgram\GetAmountApprovedAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Query\Comparison;
use CRM_Funding_ExtensionUtil as E;

final class GetAmountApprovedHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingProgram';

  private FundingCaseManager $fundingCaseManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(FundingCaseManager $fundingCaseManager, FundingProgramManager $fundingProgramManager) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @phpstan-return array{0: float}
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function getAmountApproved(GetAmountApprovedAction $action): array {
    if ($this->isAllowed($action)) {
      return [$this->fundingProgramManager->getAmountApproved($action->getId())];
    }

    throw new UnauthorizedException(
      sprintf('No permission to retrieve the amount approved for funding program %d', $action->getId())
    );
  }

  /**
   * The action is allowed if the contact has access to the funding program
   * itself or to at least one funding case that uses this funding program.
   *
   * @throws \CRM_Core_Exception
   */
  private function isAllowed(GetAmountApprovedAction $action): bool {
    return NULL !== $this->fundingProgramManager->getIfAllowed($action->getId())
      || NULL !== $this->fundingCaseManager->getFirstBy(
        Comparison::new('funding_program_id', '=', $action->getId())
      );
  }

}
