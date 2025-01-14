<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\PayoutProcess\Api4\ActionHandler\Drawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\Drawdown\CreateAction;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

class RemoteCreateActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingDrawdown';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function create(CreateAction $action): Result {
    return $this->api4->execute(FundingDrawdown::getEntityName(), 'create', [
      'values' => DrawdownEntity::fromArray([
        'payout_process_id' => $action->getPayoutProcessId(),
        'status' => 'new',
        'creation_date' => date('Y-m-d H:i:s'),
        'amount' => $action->getAmount(),
        'acception_date' => NULL,
        'requester_contact_id' => $action->getResolvedContactId(),
        'reviewer_contact_id' => NULL,
      ])->toArray(),
    ]);
  }

}
