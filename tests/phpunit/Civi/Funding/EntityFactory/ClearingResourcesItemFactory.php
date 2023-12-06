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

use Civi\Funding\Entity\ClearingResourcesItemEntity;

/**
 * @phpstan-type clearingResourcesItemT array{
 *   id?: int,
 *   clearing_process_id?: int,
 *   application_resources_item_id?: int,
 *   status?: string,
 *   file_id?: ?int,
 *   amount?: float,
 *   amount_admitted?: ?float,
 *   description?: string,
 * }
 */
final class ClearingResourcesItemFactory {

  /**
   * @phpstan-param clearingResourcesItemT $values
   */
  public static function create(array $values = []): ClearingResourcesItemEntity {
    $values += [
      'clearing_process_id' => ClearingProcessFactory::DEFAULT_ID,
      'application_resources_item_id' => ApplicationResourcesItemFactory::DEFAULT_ID,
      'status' => 'new',
      'file_id' => NULL,
      'amount' => 1.23,
      'amount_admitted' => NULL,
      'description' => 'TestClearingResourcesItem',
    ];

    return ClearingResourcesItemEntity::fromArray($values);
  }

}
