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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Funding\Entity\FundingAmountApprovedChangeRequestEntity;

final class FundingAmountApprovedChangeRequestFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(
    int $fundingCaseId,
    int $creationContactId,
    array $values = []
  ): FundingAmountApprovedChangeRequestEntity {
    $result = (new DAOCreateAction(FundingAmountApprovedChangeRequest::getEntityName(), 'create'))
      ->setCheckPermissions(FALSE)
      ->setValues($values + [
        'funding_case_id' => $fundingCaseId,
        'status' => 'new',
        'creation_date' => date('Y-m-d H:i:s'),
        'creation_contact_id' => $creationContactId,
        'amount_requested' => 120.0,
        'comment' => NULL,
        'decision_date' => NULL,
        'decision_contact_id' => NULL,
        'amount_approved' => NULL,
      ])->execute();

    return FundingAmountApprovedChangeRequestEntity::singleFromApiResult($result)->reformatDates();
  }

}
