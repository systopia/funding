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

namespace Civi\Funding\ApplicationProcess;

final class ApplicationProcessPermissions {

  public const REVIEW_CALCULATIVE = 'review_calculative';

  public const REVIEW_CONTENT = 'review_content';

  /**
   * @phpstan-param list<string> $permissions
   */
  public static function hasReviewPermission(array $permissions): bool {
    return self::hasReviewCalculativePermission($permissions) || self::hasReviewContentPermission($permissions);
  }

  /**
   * @phpstan-param list<string> $permissions
   */
  public static function hasReviewCalculativePermission(array $permissions): bool {
    return in_array(self::REVIEW_CALCULATIVE, $permissions, TRUE);
  }

  /**
   * @phpstan-param list<string> $permissions
   */
  public static function hasReviewContentPermission(array $permissions): bool {
    return in_array(self::REVIEW_CONTENT, $permissions, TRUE);
  }

}
