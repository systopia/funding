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

use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;

trait NewApplicationFormActionTrait {

  protected FundingCaseTypeProgramRelationChecker $_relationChecker;

  /**
   * @throws \API_Exception
   */
  private function assertFundingCaseTypeAndProgramRelated(int $fundingCaseTypeId, int $fundingProgramId): void {
    if (!$this->_relationChecker->areFundingCaseTypeAndProgramRelated($fundingCaseTypeId, $fundingProgramId)) {
      throw new \API_Exception('Funding program and funding case type are not related', 'invalid_arguments');
    }
  }

  /**
   * @param array{requests_start_date: string|null, requests_end_date: string|null} $fundingProgram
   *
   * @throws \API_Exception
   */
  protected function assertFundingProgramDates(array $fundingProgram): void {
    if (NULL !== $fundingProgram['requests_start_date'] && date('Y-m-d') < $fundingProgram['requests_start_date']) {
      throw new \API_Exception(sprintf(
        'Funding program does not allow applications before %s',
        $fundingProgram['requests_start_date']
      ), 'invalid_arguments');
    }

    if (NULL !== $fundingProgram['requests_end_date'] && date('Y-m-d') > $fundingProgram['requests_end_date']) {
      throw new \API_Exception(sprintf(
        'Funding program does not allow applications after %s',
        $fundingProgram['requests_end_date']
      ), 'invalid_arguments');
    }
  }

}
