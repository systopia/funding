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

namespace Civi\Funding;

final class ActivityTypeIds {

  public const FUNDING_APPLICATION_CREATE = 63785200;

  public const FUNDING_APPLICATION_STATUS_CHANGE = 63785201;

  public const FUNDING_APPLICATION_COMMENT_INTERNAL = 63785202;

  public const FUNDING_APPLICATION_REVIEW_STATUS_CHANGE = 63785203;

  public const FUNDING_APPLICATION_COMMENT_EXTERNAL = 63785204;

  public const FUNDING_APPLICATION_TASK_INTERNAL = 63785205;

  public const FUNDING_APPLICATION_TASK_EXTERNAL = 63785206;

  public const FUNDING_APPLICATION_RESTORE = 63785207;

  /**
   * @phpstan-return array<string, int>
   *
   * @codeCoverageIgnore
   */
  public static function getIds(): array {
    static $ids = NULL;
    if (NULL === $ids) {
      $ids = (new \ReflectionClass(__CLASS__))->getConstants();
    }

    return $ids;
  }

}
