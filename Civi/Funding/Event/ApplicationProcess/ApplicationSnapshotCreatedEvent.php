<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Event\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ApplicationSnapshotEntity;

final class ApplicationSnapshotCreatedEvent extends AbstractApplicationEvent {

  private ApplicationSnapshotEntity $applicationSnapshot;

  public function __construct(
    ApplicationSnapshotEntity $applicationSnapshot,
    ApplicationProcessEntityBundle $applicationProcessBundle
  ) {
    parent::__construct($applicationProcessBundle);
    $this->applicationSnapshot = $applicationSnapshot;
  }

  public function getApplicationSnapshot(): ApplicationSnapshotEntity {
    return $this->applicationSnapshot;
  }

}
