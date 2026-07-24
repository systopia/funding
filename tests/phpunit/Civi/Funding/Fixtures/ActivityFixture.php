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

use Civi\Api4\Activity;
use Civi\Api4\EntityActivity;
use Civi\Api4\FundingApplicationProcess;

final class ActivityFixture {

  /**
   * @phpstan-param array<string, scalar> $values
   *
   * @phpstan-return array{id: int}&array<string, scalar|null>
   *
   * @throws \CRM_Core_Exception
   */
  public static function addApplicationProcessFixture(
    int $applicationProcessId,
    int|string $activityType,
    int $sourceContactId,
    array $values = []
  ): array {
    if (is_string($activityType)) {
      $values['activity_type_id:name'] = $activityType;
    }
    else {
      $values['activity_type_id'] = $activityType;
    }

    /** @phpstan-var array{id: int} $activityValues */
    $activityValues = Activity::create(FALSE)
      ->setValues([
        'source_contact_id' => $sourceContactId,
      ] + $values)->execute()->single();

    if (isset($values['created_date'])) {
      // CiviCRM ignores a given activity created_date so we have to set it directly.
      \CRM_Core_DAO::executeQuery('UPDATE civicrm_activity SET created_date = %1 WHERE id = %2', [
        1 => [$values['created_date'], 'String'],
        2 => [$activityValues['id'], 'Integer'],
      ]);
      $activityValues['created_date'] = $values['created_date'];
    }

    if (isset($values['modified_date'])) {
      // CiviCRM ignores a given activity modified_date so we have to set it directly.
      \CRM_Core_DAO::executeQuery('UPDATE civicrm_activity SET modified_date = %1 WHERE id = %2', [
        1 => [$values['modified_date'], 'String'],
        2 => [$activityValues['id'], 'Integer'],
      ]);
      $activityValues['modified_date'] = $values['modified_date'];
    }

    EntityActivity::connect(FALSE)
      ->setActivityId($activityValues['id'])
      ->setEntity(FundingApplicationProcess::getEntityName())
      ->setEntityId($applicationProcessId)
      ->execute();

    return $activityValues;
  }

}
