<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingTask;
use Civi\Funding\Entity\FundingTaskEntity;

final class FundingTaskFixture {

  /**
   * @param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(
    int $sourceContactId,
    int $fundingCaseId,
    string $affectedIdentifier,
    array $values = []
  ): FundingTaskEntity {
    // @phpstan-ignore argument.type
    $task = FundingTaskEntity::newTask($values + [
      'subject' => 'Test',
      'affected_identifier' => $affectedIdentifier,
      'required_permissions' => NULL,
      'type' => 'test',
      'funding_case_id' => $fundingCaseId,
    ]);

    $newValues = FundingTask::create(FALSE)
      ->setValues(['source_contact_id' => $sourceContactId] + $task->toPersistArray())
      ->execute()
      ->single();

    // @phpstan-ignore argument.type
    $task->setValues($task->toArray() + $newValues);

    return $task;
  }

}
