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

namespace Civi\Funding\Fixtures;

use Civi\Api4\EntityFile;

/**
 * @phpstan-type entityFileT array{
 *   id: int,
 *   entity_table: string,
 *   entity_id: int,
 *   file_id: int,
 * }
 */
final class EntityFileFixture {

  /**
   * @phpstan-return entityFileT
   */
  public static function addFixture(string $entityTable, int $entityId, int $fileId): array {
    $action = EntityFile::create(FALSE)
      ->setValues([
        'entity_table' => $entityTable,
        'entity_id' => $entityId,
        'file_id' => $fileId,
      ]);

    $values = $action->execute()->single();
    // Unset extra values returned on create action since CiviCRM 5.53.
    unset($values['custom']);
    unset($values['check_permissions']);

    /** @phpstan-var entityFileT $values */
    return $values;
  }

}
