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

namespace Civi\Funding\Api4\Action\Traits;

use Civi\RemoteTools\Api4\Util\SelectUtil;

trait IsFieldSelectedTrait {

  /**
   * For DAO entities isFieldExplicitlySelected() has to be used for fields of
   * type "Extra". Those fields are not part of the result if "*" is selected.
   *
   * @see isFieldExplicitlySelected()
   */
  protected function isFieldSelected(string $fieldName): bool {
    return SelectUtil::isFieldSelected($fieldName, $this->getSelect());
  }

  protected function isFieldExplicitlySelected(string $fieldName): bool {
    return in_array($fieldName, $this->getSelect(), TRUE);
  }

  protected function isRowCountSelected(): bool {
    return $this->isFieldExplicitlySelected('row_count');
  }

  protected function isRowCountSelectedOnly(): bool {
    return ['row_count'] === $this->getSelect();
  }

}
