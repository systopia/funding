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

use Civi\Funding\Api4\Permissions;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\AbstractApplicationActionsDeterminerDecorator;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\DefaultApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;
use Civi\Funding\Permission\CiviPermissionChecker;

final class PersonalkostenApplicationActionsDeterminer extends AbstractApplicationActionsDeterminerDecorator {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private CiviPermissionChecker $civiPermissionChecker;

  public function __construct(CiviPermissionChecker $civiPermissionChecker) {
    $this->civiPermissionChecker = $civiPermissionChecker;
    parent::__construct(
      new ReworkPossibleApplicationProcessActionsDeterminer(new DefaultApplicationProcessActionsDeterminer())
    );
  }

  public function getActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    $actions = parent::getActions($applicationProcessBundle, $statusList);

    if (
      $applicationProcessBundle->getFundingCase()->getStatus() !== FundingCaseStatus::CLEARED
      && !in_array('update', $actions, TRUE)
      && $this->civiPermissionChecker->checkPermission(Permissions::ADMINISTER_FUNDING)
    ) {
      // Required for change of "Förderquote" and "Sachkostenpauschale" of funding program.
      $actions[] = 'update';
    }

    return $actions;
  }

}
