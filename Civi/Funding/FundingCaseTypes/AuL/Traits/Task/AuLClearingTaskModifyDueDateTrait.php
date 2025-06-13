<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\AuL\Traits\Task;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;

trait AuLClearingTaskModifyDueDateTrait {

  protected function getDueDate(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess,
    FundingTaskEntity $task
  ): ?\DateTimeInterface {
    return $applicationProcessBundle->getApplicationProcess()->getEndDate()
      ?->add(new \DateInterval(AuLClearingTaskConstants::DUE_DATE_DELAY));
  }

}
