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

namespace Civi\Funding\FundingCase\StatusDeterminer;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;

interface FundingCaseStatusDeterminerInterface {

  public const SERVICE_TAG = 'funding.case.status_determiner';

  /**
   * @phpstan-return list<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  public function getStatus(string $currentStatus, string $action): string;

  /**
   * If an application process status is changed the return value of this
   * method will be used as new funding case status. If the funding case status
   * should not change, the current status has to be returned.
   */
  public function getStatusOnApplicationProcessStatusChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    string $previousStatus
  ): string;

}
