<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingTaskEntity;

/**
 * @phpstan-type newFundingTaskT array{
 *    subject?: string,
 *    details?: string|null,
 *    'status_id:name'?: string,
 *    assignee_contact_ids?: list<int>,
 *    required_permissions?: list<string>|null,
 *    type?: string,
 *    affected_identifier?: string,
 *    funding_case_id?: int,
 *    application_process_id?: int,
 *    clearing_process_id?: int,
 *  }
 */
final class FundingTaskFactory {

  /**
   * @phpstan-param newFundingTaskT $values
   */
  public static function create(array $values = []): FundingTaskEntity {
    $values += [
      'subject' => 'Test Task',
      'status_id:name' => ActivityStatusNames::SCHEDULED,
      'required_permissions' => NULL,
      'type' => 'test',
      'affected_identifier' => FundingCaseFactory::DEFAULT_IDENTIFIER,
      'funding_case_id' => FundingCaseFactory::DEFAULT_ID,
    ];

    return FundingTaskEntity::newTask($values);
  }

}
