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

namespace Civi\Funding\Database\Util;

final class EntityNameUtil {

  /**
   * @param string $eventEntity Entity attribute of pre/post event.
   */
  public static function getEntityNameForEventEntity(string $eventEntity): string {
    return in_array($eventEntity, ['Individual', 'Organization', 'Household'], TRUE)
      ? 'Contact' : $eventEntity;
  }

}
