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

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class PayoutProcessCloseSubscriber implements EventSubscriberInterface {

  private PayoutProcessManager $payoutProcessManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [DrawdownAcceptedEvent::class => 'onAccepted'];
  }

  public function __construct(PayoutProcessManager $payoutProcessManager) {
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onAccepted(DrawdownAcceptedEvent $event): void {
    $drawdown = $event->getDrawdown();
    $payoutProcess = $this->payoutProcessManager->get($drawdown->getPayoutProcessId());
    Assert::notNull($payoutProcess);

    if ($payoutProcess->getAmountTotal() === $this->payoutProcessManager->getAmountAccepted($payoutProcess)) {
      $this->payoutProcessManager->close($payoutProcess);
    }
  }

}
