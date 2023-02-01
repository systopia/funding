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
 *   id?: int|null,
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
 *   reviewer_cont_contact_id?: int|null,
 *   is_review_calculative?: bool|null,
 *   reviewer_calc_contact_id?: int|null,
 *   check_permissions?: bool,
 *   custom?: mixed,
 * }
 */
final class ApplicationProcessFactory {

  public const DEFAULT_ID = 2;

  /**
   * @phpstan-param applicationProcessValuesT $values
   */
  public static function createApplicationProcess(array $values = []): ApplicationProcessEntity {
    $values += [
      'id' => self::DEFAULT_ID,
      'identifier' => 'app2',
      'funding_case_id' => FundingCaseFactory::DEFAULT_ID,
      'status' => 'new_status',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['foo' => 'bar'],
      'amount_requested' => 1.2,
      'creation_date' => '2023-01-01 01:01:01',
      'modification_date' => '2023-01-02 02:02:02',
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'reviewer_cont_contact_id' => NULL,
      'is_review_calculative' => NULL,
      'reviewer_calc_contact_id' => NULL,
    ];
    if (NULL === $values['id']) {
      unset($values['id']);
      $values['identifier'] = '';
    }

    return ApplicationProcessEntity::fromArray($values);
  }

}
