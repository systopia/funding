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

namespace Civi\Funding\Database;

/**
 * @codeCoverageIgnore
 */
final class DaoEntityInfoProvider {

  public function getTable(string $entityName): string {
    // Return type was changed from string to ?string in CiviCRM 6.3.0. To avoid
    // an error in phpstan when testing with a previous version, the phpdoc type
    // hint is required.
    /** @var string|null $table */
    $table = \CRM_Core_DAO_AllCoreTables::getTableForEntityName($entityName);
    if (NULL === $table) {
      throw new \InvalidArgumentException(sprintf('Unknown entity "%s"', $entityName));
    }

    return $table;
  }

}
