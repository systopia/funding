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

namespace Civi\Funding\ClearingProcess;

final class ClearingProcessPermissions {

  public const CLEARING_APPLY = 'clearing_apply';

  public const CLEARING_MODIFY = 'clearing_modify';

  // Required to change admitted values.
  public const REVIEW_CALCULATIVE = 'review_clearing_calculative';

  public const REVIEW_CONTENT = 'review_clearing_content';

  // Required to change content of clearing when in review.
  public const REVIEW_AMEND = 'review_clearing_amend';

  /**
   * @phpstan-param list<string> $permissions
   */
  public static function hasAnyReviewPermission(array $permissions): bool {
    return [] !== array_intersect($permissions, [
      self::REVIEW_CALCULATIVE,
      self::REVIEW_CONTENT,
      self::REVIEW_AMEND,
    ]);
  }

}
