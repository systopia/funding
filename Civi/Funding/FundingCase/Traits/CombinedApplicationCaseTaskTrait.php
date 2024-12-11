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

namespace Civi\Funding\FundingCase\Traits;

trait CombinedApplicationCaseTaskTrait {

  protected static string $taskType = 'apply';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  /**
   * @phpstan-return non-empty-list<string>
   *   List of status in which an application process can be applied.
   */
  protected function getAppliableStatusList(): array {
    return ['draft', 'new', 'rework'];
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to apply an application.
   */
  protected function getRequiredPermissions(): array {
    return ['application_apply'];
  }

}
