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

use Civi\Api4\FundingDrawdown;
use Civi\Api4\FundingPayoutProcess;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\Entity\PayoutProcessBundle;
use Civi\Funding\Event\PayoutProcess\PayoutProcessCreatedEvent;
use Civi\Funding\Event\PayoutProcess\PayoutProcessPreUpdateEvent;
use Civi\Funding\Event\PayoutProcess\PayoutProcessUpdatedEvent;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Webmozart\Assert\Assert;

class PayoutProcessManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    Api4Interface $api4,
    CiviEventDispatcherInterface $eventDispatcher,
    FundingCaseManager $fundingCaseManager
  ) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function close(PayoutProcessBundle $payoutProcessBundle): void {
    $payoutProcessBundle->getPayoutProcess()->setStatus('closed');
    $payoutProcessBundle->getPayoutProcess()->setModificationDate(new \DateTime(\CRM_Utils_Time::date('Y-m-d H:i:s')));
    $this->update($payoutProcessBundle);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function create(FundingCaseEntity $fundingCase, float $amountTotal): PayoutProcessEntity {
    $now = \CRM_Utils_Time::date('Y-m-d H:i:s');
    $result = $this->api4->createEntity(FundingPayoutProcess::getEntityName(), [
      'funding_case_id' => $fundingCase->getId(),
      'status' => 'open',
      'creation_date' => $now,
      'modification_date' => $now,
      'amount_total' => $amountTotal,
    ]);

    $payoutProcess = PayoutProcessEntity::singleFromApiResult($result);

    $event = new PayoutProcessCreatedEvent($fundingCase, $payoutProcess);
    $this->eventDispatcher->dispatch(PayoutProcessCreatedEvent::class, $event);

    return $payoutProcess;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?PayoutProcessEntity {
    $result = $this->api4->getEntities(
      FundingPayoutProcess::getEntityName(),
      Comparison::new('id', '=', $id)
    );

    return PayoutProcessEntity::singleOrNullFromApiResult($result);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getBundle(int $id): ?PayoutProcessBundle {
    $payoutProcess = $this->get($id);

    if (NULL === $payoutProcess) {
      return NULL;
    }

    $fundingCaseBundle = $this->fundingCaseManager->getBundle($payoutProcess->getFundingCaseId());
    Assert::notNull($fundingCaseBundle);

    return new PayoutProcessBundle($payoutProcess, $fundingCaseBundle);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function updateAmountTotal(PayoutProcessBundle $payoutProcessBundle, float $amountTotal): void {
    $payoutProcessBundle->getPayoutProcess()->setAmountTotal($amountTotal);
    $payoutProcessBundle->getPayoutProcess()->setModificationDate(new \DateTime(\CRM_Utils_Time::date('YmdHis')));
    $this->update($payoutProcessBundle);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getAmountAccepted(PayoutProcessEntity $payoutProcess): float {
    $action = FundingDrawdown::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('SUM(amount) AS amountSum')
      ->addWhere('payout_process_id', '=', $payoutProcess->getId())
      ->addWhere('status', '=', 'accepted')
      ->addGroupBy('payout_process_id');

    return round($this->api4->executeAction($action)->first()['amountSum'] ?? 0.0, 2);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getAmountPaidOut(PayoutProcessEntity $payoutProcess): float {
    $action = FundingDrawdown::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('SUM(amount) AS amountSum')
      ->addWhere('payout_process_id', '=', $payoutProcess->getId())
      ->addWhere('status', '=', 'accepted')
      ->addWhere('amount', '>', 0)
      ->addGroupBy('payout_process_id');

    return round($this->api4->executeAction($action)->first()['amountSum'] ?? 0.0, 2);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getAmountAvailable(PayoutProcessEntity $payoutProcess): float {
    return $payoutProcess->getAmountTotal() - $this->getAmountRequested($payoutProcess);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getAmountRequested(PayoutProcessEntity $payoutProcess): float {
    $action = FundingDrawdown::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('SUM(amount) AS amountSum')
      ->addWhere('payout_process_id', '=', $payoutProcess->getId())
      ->addGroupBy('payout_process_id');

    return round($this->api4->executeAction($action)->first()['amountSum'] ?? 0.0, 2);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getLastBundleByFundingCaseId(int $fundingCaseId): ?PayoutProcessBundle {
    $payoutProcess = $this->getLastByFundingCaseId($fundingCaseId);
    if (NULL === $payoutProcess) {
      return NULL;
    }

    $fundingCaseBundle = $this->fundingCaseManager->getBundle($payoutProcess->getFundingCaseId());
    Assert::notNull($fundingCaseBundle);

    return new PayoutProcessBundle($payoutProcess, $fundingCaseBundle);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getLastByFundingCaseId(int $fundingCaseId): ?PayoutProcessEntity {
    $result = $this->api4->getEntities(
      FundingPayoutProcess::getEntityName(),
      Comparison::new('funding_case_id', '=', $fundingCaseId),
      ['id' => 'DESC'],
      1
    );

    return PayoutProcessEntity::singleOrNullFromApiResult($result);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function hasAccess(int $id): bool {
    return $this->api4->countEntities(
      FundingPayoutProcess::getEntityName(),
      Comparison::new('id', '=', $id)
    ) === 1;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function update(PayoutProcessBundle $payoutProcessBundle): void {
    $event = new PayoutProcessPreUpdateEvent($payoutProcessBundle);
    $this->eventDispatcher->dispatch(PayoutProcessPreUpdateEvent::class, $event);

    $payoutProcess = $payoutProcessBundle->getPayoutProcess();
    $this->api4->updateEntity(
      FundingPayoutProcess::getEntityName(),
      $payoutProcess->getId(),
      $payoutProcess->toArray()
    );

    $event = new PayoutProcessUpdatedEvent($payoutProcessBundle);
    $this->eventDispatcher->dispatch(PayoutProcessUpdatedEvent::class, $event);
  }

}
