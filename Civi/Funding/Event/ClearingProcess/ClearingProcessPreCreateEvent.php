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

namespace Civi\Funding\Event\ClearingProcess;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\Traits\ApplicationProcessEntityBundleTrait;

final class ClearingProcessPreCreateEvent {

  use ApplicationProcessEntityBundleTrait;

  private ClearingProcessEntity $clearingProcess;

  public function __construct(
    ClearingProcessEntity $clearingProcess,
    ApplicationProcessEntityBundle $applicationProcessBundle
  ) {
    $this->clearingProcess = $clearingProcess;
    $this->applicationProcessBundle = $applicationProcessBundle;
  }

  public function getClearingProcess(): ClearingProcessEntity {
    return $this->clearingProcess;
  }

}
