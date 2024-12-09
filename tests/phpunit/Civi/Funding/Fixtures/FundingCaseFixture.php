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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingCase;
use Civi\Funding\Entity\FundingCaseEntity;

final class FundingCaseFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $fundingProgramId, int $fundingCaseTypeId,
    int $recipientContactId, int $creationContactId, array $values = []
  ): FundingCaseEntity {
    static $count = 1;

    $now = date('Y-m-d H:i:s');

    $result = FundingCase::create(FALSE)
      ->setValues($values + [
        'identifier' => 'test' . $count,
        'funding_program_id' => $fundingProgramId,
        'funding_case_type_id' => $fundingCaseTypeId,
        'recipient_contact_id' => $recipientContactId,
        'status' => 'open',
        'creation_date' => $now,
        'modification_date' => $now,
        'creation_contact_id' => $creationContactId,
        'notification_contact_ids' => [$creationContactId],
        'amount_approved' => NULL,
      ])->execute();

    ++$count;

    return FundingCaseEntity::singleFromApiResult($result)->reformatDates();
  }

}
