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

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\FundingCase\Actions\FundingCaseActions as Actions;

final class SetRecipientContactActionsDeterminer extends FundingCaseActionsDeterminer {

  private const STATUS_PERMISSIONS_ACTION_MAP = [
    'open' => [
      'review_calculative' => [Actions::SET_RECIPIENT_CONTACT],
      'review_content' => [Actions::SET_RECIPIENT_CONTACT],
    ],
  ];

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  public function __construct(FundingCaseActionsDeterminerInterface $actionsDeterminer) {
    parent::__construct(self::STATUS_PERMISSIONS_ACTION_MAP);
    $this->actionsDeterminer = $actionsDeterminer;
  }

  public function getActions(string $status, array $applicationProcessStatusList, array $permissions): array {
    return array_values(array_unique(array_merge(
      $this->actionsDeterminer->getActions($status, $applicationProcessStatusList, $permissions),
      parent::getActions($status, $applicationProcessStatusList, $permissions)
    )));
  }

}
