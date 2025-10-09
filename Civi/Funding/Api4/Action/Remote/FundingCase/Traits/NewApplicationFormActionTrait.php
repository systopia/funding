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

namespace Civi\Funding\Api4\Action\Remote\FundingCase\Traits;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use CRM_Funding_ExtensionUtil as E;

trait NewApplicationFormActionTrait {

  protected ?FundingCaseTypeProgramRelationChecker $_relationChecker;

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function assertCreateApplicationPermission(FundingProgramEntity $fundingProgram): void {
    if (!in_array('application_create', $fundingProgram->getPermissions(), TRUE)) {
      throw new UnauthorizedException(E::ts('Required permission is missing'));
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function assertFundingCaseTypeAndProgramRelated(int $fundingCaseTypeId, int $fundingProgramId): void {
    if (!$this->getRelationChecker()->areFundingCaseTypeAndProgramRelated($fundingCaseTypeId, $fundingProgramId)) {
      throw new FundingException(E::ts('Funding program and funding case type are not related'), 'invalid_arguments');
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function assertFundingProgramDates(FundingProgramEntity $fundingProgram): void {
    if (new \DateTime(date('Y-m-d')) < $fundingProgram->getRequestsStartDate()) {
      throw new FundingException(E::ts(
        'Funding program does not allow applications before %1',
        [1 => $fundingProgram->getRequestsStartDate()->format(E::ts('Y-m-d'))]
      ), 'invalid_arguments');
    }

    if (new \DateTime(date('Y-m-d')) > $fundingProgram->getRequestsEndDate()) {
      throw new FundingException(E::ts(
        'Funding program does not allow applications after %1',
        [1 => $fundingProgram->getRequestsEndDate()->format(E::ts('Y-m-d'))]
      ), 'invalid_arguments');
    }
  }

  protected function getRelationChecker(): FundingCaseTypeProgramRelationChecker {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->_relationChecker ??= \Civi::service(FundingCaseTypeProgramRelationChecker::class);
  }

}
