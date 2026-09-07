<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest;

use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Api4\FundingCase;
use Webmozart\Assert\Assert;

/**
 * @method $this setIds(list<int> $ids)
 */
class ApproveAction extends AbstractFundingAmountApprovedChangeRequestAction {

  public function __construct() {
    parent::__construct(FundingAmountApprovedChangeRequest::getEntityName(), 'approve');
  }

  /**
   * @param int $id
   * @param array<string, mixed> $request
   *
   * @return array<string, mixed>
   */
  protected function processRequest(int $id, array $request): array {
    Assert::numeric($request['amount_requested']);
    $amount = (float) $request['amount_requested'];

    Assert::integerish($request['funding_case_id']);
    $fundingCaseId = (int) $request['funding_case_id'];

    FundingAmountApprovedChangeRequest::update(FALSE)
      ->addWhere('id', '=', $id)
      ->setValues([
        'status' => 'approved',
        'amount_approved' => $amount,
        'decision_date' => date('Y-m-d H:i:s'),
        'decision_contact_id' => \CRM_Core_Session::getLoggedInContactID(),
      ])
      ->execute();

    FundingCase::update(FALSE)
      ->addWhere('id', '=', $fundingCaseId)
      ->setValues(['amount_approved' => $amount])
      ->execute();

    FundingCase::recreateTransferContract()
      ->setId($fundingCaseId)
      ->execute();

    return [
      'id' => $id,
      'status' => 'approved',
      'amount_approved' => $amount,
    ];
  }

}
