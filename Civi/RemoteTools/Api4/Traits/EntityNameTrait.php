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

namespace Civi\RemoteTools\Api4\Traits;

/**
 * Makes protected static method getEntityName() of class AbstractEntity public.
 *
 * @see \Civi\Api4\Generic\AbstractEntity::getEntityName()
 */
trait EntityNameTrait {

  /**
   * Get entity name from called class.
   *
   * The "_" prefix is required so the method is not recognized as action method.
   */
  public static function _getEntityName(): string {
    return parent::getEntityName();
  }

}
