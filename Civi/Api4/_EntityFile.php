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

namespace Civi\Api4;

// phpcs:disable PSR1.Files.SideEffects
if (class_exists('Civi\Api4\EntityFile')) {
  class_alias('Civi\Api4\EntityFile', 'Civi\Api4\_EntityFile');
}
else {
  /**
   * In core there's no EntityFile entity, yet. So this class is used as
   * replacement.
   *
   * @searchable bridge
   *
   * @see https://github.com/civicrm/civicrm-core/pull/25845
   */
  final class _EntityFile extends Generic\DAOEntity {

    use Generic\Traits\EntityBridge;

    public static function getEntityName(): string {
      return 'EntityFile';
    }

  }
}
