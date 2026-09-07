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

/**
 * @method $this setIds(list<int> $ids)
 */
class RejectAction extends AbstractFundingAmountApprovedChangeRequestAction {

  public function __construct() {
    parent::__construct(FundingAmountApprovedChangeRequest::getEntityName(), 'reject');
  }

  /**
   * @param int $id
   * @param array<string, mixed> $request
   *
   * @return array<string, mixed>
   */
  protected function processRequest(int $id, array $request): array {
    FundingAmountApprovedChangeRequest::update(FALSE)
      ->addWhere('id', '=', $id)
      ->setValues(['status' => 'rejected'])
      ->execute();

    return [
      'id' => $id,
      'status' => 'rejected',
      'amount_approved' => NULL,
    ];
  }

}
