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

use Civi\Funding\Entity\ApplicationProcessEntity;

/**
 * @phpstan-type applicationProcessValuesT array{
 *   id?: int,
 *   funding_case_id?: int,
 *   status?: string,
 *   creation_date?: string,
 *   modification_date?: string,
 *   title?: string,
 *   short_description?: string,
 *   start_date?: string|null,
 *   end_date?: string|null,
 *   request_data?: array<string, mixed>,
 *   amount_requested?: float,
 *   amount_granted?: float|null,
 *   granted_budget?: float|null,
 *   is_review_content?: bool|null,
 *   is_review_calculative?: bool|null,
 *   check_permissions?: bool,
 *   custom?: mixed,
 * }
 */
final class ApplicationProcessFactory {

  /**
   * @phpstan-param applicationProcessValuesT $values
   */
  public static function createApplicationProcess(array $values = []): ApplicationProcessEntity {
    return ApplicationProcessEntity::fromArray($values + [
      'id' => 2,
      'funding_case_id' => 3,
      'status' => 'new_status',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['foo' => 'bar'],
      'amount_requested' => 1.2,
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);
  }

}
