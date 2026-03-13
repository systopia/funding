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
 * @phpstan-import-type applicationCostItemT from \Civi\Funding\Entity\ApplicationCostItemEntity
 * @phpstan-import-type applicationResourcesItemT from \Civi\Funding\Entity\ApplicationResourcesItemEntity
 *
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
 *   cost_items?: array<applicationCostItemT>,
 *   resources_items?: array<applicationResourcesItemT>,
 *   amount_requested?: float,
 *   amount_eligible?: float,
 *   is_review_content?: ?bool,
 *   is_review_calculative?: ?bool,
 *   is_eligible?: ?bool,
 *   is_in_work?: bool,
 *   is_rejected?: bool,
 *   is_withdrawn?: bool,
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
      'cost_items' => [],
      'resources_items' => [],
      'amount_requested' => 123.45,
      'amount_eligible' => 123.45,
      'is_review_content' => TRUE,
      'is_review_calculative' => TRUE,
      'is_eligible' => TRUE,
      'is_in_work' => FALSE,
      'is_rejected' => FALSE,
      'is_withdrawn' => FALSE,
      'custom_fields' => [],
    ]);
  }

}
