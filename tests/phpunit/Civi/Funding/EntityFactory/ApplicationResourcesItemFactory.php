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

use Civi\Funding\Entity\ApplicationResourcesItemEntity;

/**
 * @phpstan-type applicationResourcesItemT array{
 *   id?: int,
 *   application_process_id?: int,
 *   identifier?: string,
 *   type?: string,
 *   amount?: float,
 *   properties?: array<int|string, mixed>,
 *   data_pointer?: string,
 * }
 */
final class ApplicationResourcesItemFactory {

  /**
   * @phpstan-param applicationResourcesItemT $values
   */
  public static function createApplicationResourcesItem(array $values = []): ApplicationResourcesItemEntity {
    $values += [
      'application_process_id' => ApplicationProcessFactory::DEFAULT_ID,
      'identifier' => 'test-resources-item',
      'type' => 'test',
      'amount' => 1.23,
      'properties' => [],
      'data_pointer' => '/test',
    ];

    return ApplicationResourcesItemEntity::fromArray($values);
  }

}
