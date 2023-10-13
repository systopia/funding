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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\ApplicationSnapshotEntity;

/**
 * @phpstan-type applicationSnapshotT array{
 *   id?: int,
 *   application_process_id?: int,
 *   status?: string,
 *   creation_date?: string,
 *   title?: string,
 *   short_description?: string,
 *   start_date?: ?string,
 *   end_date?: ?string,
 *   request_data?: array<string, mixed>,
 *   amount_requested?: double,
 *   amount_granted?: ?double,
 *   granted_budget?: ?double,
 *   is_review_content?: ?bool,
 *   is_review_calculative?: ?bool,
 *   is_eligible?: ?bool,
 * }
 */
final class ApplicationSnapshotFactory {

  public const DEFAULT_ID = 12;

  /**
   * @phpstan-param applicationSnapshotT $values
   */
  public static function createApplicationSnapshot(array $values = []): ApplicationSnapshotEntity {
    return ApplicationSnapshotEntity::fromArray($values + [
      'id' => self::DEFAULT_ID,
      'application_process_id' => ApplicationProcessFactory::DEFAULT_ID,
      'status' => 'eligible',
      'creation_date' => '2023-03-03 03:03:03',
      'title' => 'Title snapshot',
      'short_description' => 'Short description snapshot',
      'start_date' => '2023-04-04',
      'end_date' => '2023-05-05',
      'request_data' => ['foo' => 'baz'],
      'amount_requested' => 123.45,
      'amount_granted' => 111.11,
      'granted_budget' => 122.22,
      'is_review_content' => TRUE,
      'is_review_calculative' => TRUE,
      'is_eligible' => FALSE,
    ]);
  }

}
