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

use Civi\Funding\Entity\ClearingProcessEntity;

/**
 * @phpstan-type clearingProcessT array{
 *   id?: ?int,
 *   application_process_id?: int,
 *   status?: string,
 *   creation_date?: string,
 *   modification_date?: string,
 *   report_data?: array<string, mixed>,
 *   is_review_content?: bool|null,
 *   reviewer_cont_contact_id?: int|null,
 *   is_review_calculative?: bool|null,
 *   reviewer_calc_contact_id?: int|null,
 * }
 */
final class ClearingProcessFactory {

  public const DEFAULT_ID = 7;

  /**
   * @phpstan-param clearingProcessT $values
   */
  public static function create(array $values = []): ClearingProcessEntity {
    $values += [
      'id' => self::DEFAULT_ID,
      'application_process_id' => ApplicationProcessFactory::DEFAULT_ID,
      'status' => 'draft',
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'report_data' => [],
      'is_review_content' => NULL,
      'reviewer_cont_contact_id' => NULL,
      'is_review_calculative' => NULL,
      'reviewer_calc_contact_id' => NULL,
    ];
    if (NULL === $values['id']) {
      unset($values['id']);
    }

    return ClearingProcessEntity::fromArray($values);
  }

}
