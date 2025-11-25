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

namespace Civi\Funding\ApplicationProcess\StatusDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;

/**
 * Determines the status of an application process for a given action.
 */
interface ApplicationProcessStatusDeterminerInterface {

  public const SERVICE_TAG = 'funding.application.status_determiner';

  /**
   * @phpstan-return list<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  public function getInitialStatus(string $action): string;

  /**
   * The returned full status may contain the special status "@previous". In
   * this case the status gets set to the previous status according to the
   * application history.
   *
   * @throws \InvalidArgumentException
   *   If there's no status transition from the given status with the given
   *   action.
   */
  public function getStatus(FullApplicationProcessStatus $currentStatus, string $action): FullApplicationProcessStatus;

  public function getStatusOnClearingProcessStarted(
    FullApplicationProcessStatus $currentStatus
  ): FullApplicationProcessStatus;

}
