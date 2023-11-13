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

namespace Civi\Funding\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\Api4\Api4Interface;

class FundingProgramManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * This method also returns a funding program if a user has no permissions.
   *
   * @throws \CRM_Core_Exception
   *
   * @see getIfAllowed()
   */
  public function get(int $id): ?FundingProgramEntity {
    $action = FundingProgram::get(FALSE)
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addWhere('id', '=', $id);
    $result = $this->api4->executeAction($action);

    return FundingProgramEntity::singleOrNullFromApiResult($result);
  }

  /**
   * In contradiction to get() the user's permission will be checked.
   *
   * @throws \CRM_Core_Exception
   *
   * @see get()
   */
  public function getIfAllowed(int $id): ?FundingProgramEntity {
    $values = $this->api4->getEntity(FundingProgram::getEntityName(), $id);

    // @phpstan-ignore-next-line
    return NULL === $values ? NULL : FundingProgramEntity::fromArray($values);
  }

  /**
   * @return float|null
   *   The amount that has been approved for funding cases with the given
   *   funding program. The method returns a value even if a user has no
   *   permission to that funding program.
   *
   * @throws \CRM_Core_Exception
   */
  public function getAmountApproved(int $id): ?float {
    $action = FundingProgram::get(FALSE)
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_approved')
      ->addWhere('id', '=', $id);
    $result = $this->api4->executeAction($action);

    return $result->first()['amount_approved'] ?? NULL;
  }

}
