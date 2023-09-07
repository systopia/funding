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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\FundingCaseEntity;

/**
 * @phpstan-type fundingCaseValuesT array{
 *   id?: int,
 *   funding_program_id?: int,
 *   funding_case_type_id?: int,
 *   identifier?: string,
 *   status?: string,
 *   recipient_contact_id?: int,
 *   creation_date?: string,
 *   modification_date?: string,
 *   creation_contact_id?: int,
 *   amount_approved?: ?float,
 *   permissions?: array<string>,
 * }
 */
final class FundingCaseFactory {

  public const DEFAULT_ID = 3;

  /**
   * @phpstan-param fundingCaseValuesT $values
   */
  public static function createFundingCase(array $values = []): FundingCaseEntity {
    return FundingCaseEntity::fromArray($values + [
      'id' => self::DEFAULT_ID,
      'funding_program_id' => FundingProgramFactory::DEFAULT_ID,
      'funding_case_type_id' => FundingCaseTypeFactory::DEFAULT_ID,
      'identifier' => 'fc3',
      'recipient_contact_id' => 1,
      'status' => 'open',
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'creation_contact_id' => 1,
      'amount_approved' => NULL,
      'permissions' => ['test_permission'],
    ]);
  }

}
