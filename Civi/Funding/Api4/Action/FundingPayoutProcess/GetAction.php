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

namespace Civi\Funding\Api4\Action\FundingPayoutProcess;

use Civi\Api4\FundingPayoutProcess;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\FundingCase\FundingCaseManager;

final class GetAction extends DAOGetAction {

  private FundingCaseManager $fundingCaseManager;

  public function __construct(FundingCaseManager $fundingCaseManager) {
    parent::__construct(FundingPayoutProcess::getEntityName(), 'get');
    $this->fundingCaseManager = $fundingCaseManager;
  }

  public function _run(Result $result): void {
    $fundingCaseIdSelected = $this->isFundingCaseIdSelected();
    if (!$fundingCaseIdSelected) {
      $this->addSelect('funding_case_id');
    }
    parent::_run($result);

    $records = [];
    /** @phpstan-var array<string, mixed>&array{funding_case_id: int} $record */
    foreach ($result as $record) {
      if ($this->fundingCaseManager->hasAccess($record['funding_case_id'])) {
        if (!$fundingCaseIdSelected) {
          unset($record['funding_case_id']);
        }

        $records[] = $record;
      }
    }

    $result->exchangeArray($records);
    $result->setCountMatched(count($records));
  }

  private function isFundingCaseIdSelected(): bool {
    $select = $this->getSelect();

    return [] === $select || \in_array('*', $select, TRUE) || \in_array('funding_case_id', $select, TRUE);
  }

}
