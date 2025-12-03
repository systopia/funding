<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\Api;

use Civi\API\Event\PrepareEvent;
use Civi\RemoteTools\Database\TransactionFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractTransactionalApiRequestSubscriber implements EventSubscriberInterface {

  private TransactionFactory $transactionFactory;

  public function __construct(TransactionFactory $transactionFactory) {
    $this->transactionFactory = $transactionFactory;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['civi.api.prepare' => ['onApiPrepare', PHP_INT_MAX]];
  }

  public function onApiPrepare(PrepareEvent $event): void {
    if ($this->isTransactionalAction($event->getEntityName(), $event->getActionName())) {
      $event->wrapApi(function ($apiRequest, $continue) {
        $transaction = $this->transactionFactory->createTransaction();
        try {
          $result = $continue($apiRequest);
          $transaction->commit();

          return $result;
        }
        catch (\Throwable $e) {
          $transaction->rollback()->commit();

          throw $e;
        }
      });
    }
  }

  abstract protected function isTransactionalAction(string $entity, string $action): bool;

}
