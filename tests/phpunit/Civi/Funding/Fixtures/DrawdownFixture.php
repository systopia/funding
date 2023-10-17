<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Funding\Entity\DrawdownEntity;

final class DrawdownFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $payoutProcessId, int $requesterContactId, array $values = []): DrawdownEntity {
    $result = (new DAOCreateAction(FundingDrawdown::getEntityName(), 'create'))
      ->setCheckPermissions(FALSE)
      ->setValues($values + [
        'payout_process_id' => $payoutProcessId,
        'status' => 'new',
        'creation_date' => date('Y-m-d H:i:s'),
        'amount' => 1.2,
        'acception_date' => NULL,
        'requester_contact_id' => $requesterContactId,
        'reviewer_contact_id' => NULL,
      ])->execute();

    return DrawdownEntity::singleFromApiResult($result)->reformatDates();
  }

}
