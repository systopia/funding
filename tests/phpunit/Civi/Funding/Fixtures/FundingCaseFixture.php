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

/**
 * @phpstan-type fundingCaseT array{
 *   id?: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   recipient_contact_id: int,
 *   creation_date: string,
 *   modification_date: string,
 *   creation_contact_id: int,
 * }
 */
final class FundingCaseFixture {

  /**
   * @phpstan-param array<string, scalar> $values
   *
   * @throws \API_Exception
   */
  public static function addFixture(int $fundingProgramId, int $fundingCaseTypeId,
    int $recipientContactId, int $creationContactId, array $values = []
  ): FundingCaseEntity {
    $now = date('Y-m-d H:i:s');

    /** @phpstan-var fundingCaseT $fundingCaseValues */
    $fundingCaseValues = FundingCase::create()
      ->setCheckPermissions(FALSE)
      ->setValues($values + [
        'funding_program_id' => $fundingProgramId,
        'funding_case_type_id' => $fundingCaseTypeId,
        'recipient_contact_id' => $recipientContactId,
        'status' => 'open',
        'creation_date' => $now,
        'modification_date' => $now,
        'creation_contact_id' => $creationContactId,
      ])->execute()->first();

    return FundingCaseEntity::fromArray($fundingCaseValues)->reformatDates();
  }

}
