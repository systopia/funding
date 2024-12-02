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
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\PayoutProcessBundle;
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownDeletedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownPreCreateEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownPreUpdateEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Webmozart\Assert\Assert;

class DrawdownManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(
    Api4Interface $api4,
    CiviEventDispatcherInterface $eventDispatcher,
    PayoutProcessManager $payoutProcessManager
  ) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function accept(DrawdownEntity $drawdown, int $contactId): void {
    $payoutProcessBundle = $this->payoutProcessManager->getBundle($drawdown->getPayoutProcessId());
    Assert::notNull($payoutProcessBundle);

    $drawdown
      ->setReviewerContactId($contactId)
      ->setStatus('accepted')
      ->setAcceptionDate(new \DateTime(date('Y-m-d H:i:s')));

    $drawdownBundle = new DrawdownBundle($drawdown, $payoutProcessBundle);
    $this->update($drawdownBundle);

    $event = new DrawdownAcceptedEvent($drawdownBundle);
    $this->eventDispatcher->dispatch(DrawdownAcceptedEvent::class, $event);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function createNew(
    PayoutProcessBundle $payoutProcessBundle,
    float $amount,
    int $requesterContactId
  ): DrawdownEntity {
    $drawdown = DrawdownEntity::fromArray([
      'payout_process_id' => $payoutProcessBundle->getPayoutProcess()->getId(),
      'status' => 'new',
      'creation_date' => date('Y-m-d H:i:s'),
      'amount' => $amount,
      'acception_date' => NULL,
      'requester_contact_id' => $requesterContactId,
      'reviewer_contact_id' => NULL,
    ]);

    $drawdownBundle = new DrawdownBundle($drawdown, $payoutProcessBundle);
    $event = new DrawdownPreCreateEvent($drawdownBundle);
    $this->eventDispatcher->dispatch(DrawdownPreCreateEvent::class, $event);

    $result = $this->api4->createEntity(FundingDrawdown::getEntityName(), $drawdown->toArray());
    $drawdown->setValues(['id' => $result->single()['id']] + $drawdown->toArray());

    $event = new DrawdownCreatedEvent($drawdownBundle);
    $this->eventDispatcher->dispatch(DrawdownCreatedEvent::class, $event);

    return $drawdown;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function delete(DrawdownEntity $drawdown): void {
    $this->api4->deleteEntity(FundingDrawdown::getEntityName(), $drawdown->getId());

    $event = new DrawdownDeletedEvent($drawdown);
    $this->eventDispatcher->dispatch(DrawdownDeletedEvent::class, $event);
  }

  public function deleteNewDrawdownsByPayoutProcessId(int $payoutProcessId): void {
    $drawdowns = $this->getBy(CompositeCondition::fromFieldValuePairs([
      'payout_process_id' => $payoutProcessId,
      'status' => 'new',
    ]));

    foreach ($drawdowns as $drawdown) {
      $this->delete($drawdown);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?DrawdownEntity {
    $values = $this->api4->getEntity(FundingDrawdown::getEntityName(), $id);

    // @phpstan-ignore argument.type
    return NULL === $values ? NULL : DrawdownEntity::fromArray($values);
  }

  /**
   * @phpstan-return list<DrawdownEntity>
   */
  public function getBy(ConditionInterface $condition): array {
    $result = $this->api4->getEntities(
      FundingDrawdown::getEntityName(),
      $condition,
    );

    // @phpstan-ignore-next-line The result is not indexed, so it's actual a list.
    return DrawdownEntity::allFromApiResult($result);
  }

  public function insert(DrawdownEntity $drawdown): void {
    if (NULL !== $drawdown->getAcceptionDate() || 'new' !== $drawdown->getStatus()) {
      throw new \InvalidArgumentException('New drawdowns have to be in status "new"');
    }

    $result = $this->api4->createEntity(FundingDrawdown::getEntityName(), $drawdown->toArray());
    $drawdown->setValues(['id' => $result->single()['id']] + $drawdown->toArray());
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function update(DrawdownBundle $drawdownBundle): void {
    $drawdown = $drawdownBundle->getDrawdown();
    $previousDrawdown = $this->get($drawdown->getId());
    Assert::notNull($previousDrawdown);

    $event = new DrawdownPreUpdateEvent($previousDrawdown, $drawdownBundle);
    $this->eventDispatcher->dispatch(DrawdownPreUpdateEvent::class, $event);

    $this->api4->updateEntity(
      FundingDrawdown::getEntityName(),
      $drawdown->getId(),
      $drawdown->toArray()
    );

    $event = new DrawdownUpdatedEvent($previousDrawdown, $drawdownBundle);
    $this->eventDispatcher->dispatch(DrawdownUpdatedEvent::class, $event);
  }

}
