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

namespace Civi\Funding\PayoutProcess;

use Civi\Api4\PayoutProcess;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\Event\PayoutProcess\PayoutProcessCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;

class PayoutProcessManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcherInterface $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  public function create(FundingCaseEntity $fundingCase, float $amountTotal): PayoutProcessEntity {
    $result = $this->api4->createEntity(PayoutProcess::_getEntityName(), [
      'funding_case_id' => $fundingCase->getId(),
      'status' => 'open',
      'amount_total' => $amountTotal,
      'amount_paid_out' => 0.0,
    ], [
      'checkPermissions' => FALSE,
    ]);

    $payoutProcess = PayoutProcessEntity::singleFromApiResult($result);

    $event = new PayoutProcessCreatedEvent($fundingCase, $payoutProcess);
    $this->eventDispatcher->dispatch(PayoutProcessCreatedEvent::class, $event);

    return $payoutProcess;
  }

}
