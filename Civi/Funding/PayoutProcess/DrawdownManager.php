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
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownDeletedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\Api4\Query\ConditionInterface;

class DrawdownManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcherInterface $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function accept(DrawdownEntity $drawdown, int $contactId): void {
    $drawdown
      ->setReviewerContactId($contactId)
      ->setStatus('accepted')
      ->setAcceptionDate(new \DateTime(date('Y-m-d H:i:s')));

    $this->api4->updateEntity(
      FundingDrawdown::getEntityName(),
      $drawdown->getId(),
      $drawdown->toArray()
    );

    $event = new DrawdownAcceptedEvent($drawdown);
    $this->eventDispatcher->dispatch(DrawdownAcceptedEvent::class, $event);
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
    $result = $this->api4->getEntities(
      FundingDrawdown::getEntityName(),
      Comparison::new('id', '=', $id),
      [],
      1,
      0,
      ['checkPermissions' => FALSE],
    );

    return DrawdownEntity::singleOrNullFromApiResult($result);
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

}
