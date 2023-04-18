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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\PayoutProcess\PayoutProcessManager;

final class GetAction extends DAOGetAction {

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(PayoutProcessManager $payoutProcessManager) {
    parent::__construct(FundingDrawdown::_getEntityName(), 'get');
    $this->payoutProcessManager = $payoutProcessManager;
  }

  public function _run(Result $result): void {
    $payoutProcessIdSelected = $this->isPayoutProcessIdSelected();
    if (!$payoutProcessIdSelected) {
      $this->addSelect('payout_process_id');
    }
    parent::_run($result);

    $records = [];
    /** @phpstan-var array<string, mixed>&array{payout_process_id: int} $record */
    foreach ($result as $record) {
      if ($this->payoutProcessManager->hasAccess($record['payout_process_id'])) {
        if (!$payoutProcessIdSelected) {
          unset($record['payout_process_id']);
        }

        $records[] = $record;
      }
    }

    $result->exchangeArray($records);
    $result->setCountMatched(count($records));
  }

  private function isPayoutProcessIdSelected(): bool {
    $select = $this->getSelect();

    return [] === $select || \in_array('*', $select, TRUE) || \in_array('payout_process_id', $select, TRUE);
  }

}
