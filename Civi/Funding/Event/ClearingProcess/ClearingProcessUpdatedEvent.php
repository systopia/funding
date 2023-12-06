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

use Civi\Funding\Entity\ClearingProcessEntity;

final class ClearingProcessUpdatedEvent {

  private ClearingProcessEntity $previousClearingProcess;

  private ClearingProcessEntity $clearingProcess;

  public function __construct(
    ClearingProcessEntity $previousClearingProcess,
    ClearingProcessEntity $clearingProcess
  ) {
    $this->previousClearingProcess = $previousClearingProcess;
    $this->clearingProcess = $clearingProcess;
  }

  public function getPreviousClearingProcess(): ClearingProcessEntity {
    return $this->previousClearingProcess;
  }

  public function getClearingProcess(): ClearingProcessEntity {
    return $this->clearingProcess;
  }

}
