<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Actions;

use Civi\Funding\ApplicationProcess\StatusDeterminer\AbstractApplicationProcessStatusDeterminerDecorator;
use Civi\Funding\ApplicationProcess\StatusDeterminer\DefaultApplicationProcessStatusDeterminer;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;

final class PersonalkostenApplicationStatusDeterminer extends AbstractApplicationProcessStatusDeterminerDecorator {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  public function __construct() {
    parent::__construct(
      new ReworkPossibleApplicationProcessStatusDeterminer(
        new DefaultApplicationProcessStatusDeterminer()
      )
    );
  }

  public function getStatus(FullApplicationProcessStatus $currentStatus, string $action): FullApplicationProcessStatus {
    // On change of "Förderquote" or "Sachkostenpauschale" of funding program
    // "update" may happen in status not expected by the decorated status
    // determiner.
    return 'update' === $action ? $currentStatus : parent::getStatus($currentStatus, $action);
  }

}
