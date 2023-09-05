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

namespace Civi\Funding\FundingCase;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;

interface FundingCaseStatusDeterminerInterface {

  public const SERVICE_TAG = 'funding.case.status_determiner';

  public function getStatus(string $currentStatus, string $action): string;

  /**
   * @return bool
   *   TRUE when a funding case shall be closed after the status change of the
   *   given application process.
   */
  public function isClosedByApplicationProcess(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    string $previousStatus
  ): bool;

}
