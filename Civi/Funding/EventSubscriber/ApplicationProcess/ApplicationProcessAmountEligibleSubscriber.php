<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationProcessAmountEligibleSubscriber implements EventSubscriberInterface {

  /**
   * Priority is decreased so the status flags are up to date.
   */
  public const PRIORITY = ApplicationProcessStatusFlagsSubscriber::PRIORITY - 1;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreCreateEvent::class => ['onPreCreate', self::PRIORITY],
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', self::PRIORITY],
    ];
  }

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    if (TRUE === $applicationProcess->getIsEligible()) {
      $applicationProcess->setAmountEligible($applicationProcess->getAmountRequested());
    }
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    if (TRUE === $applicationProcess->getIsEligible()) {
      if (TRUE !== $event->getPreviousApplicationProcess()->getIsEligible()) {
        $applicationProcess->setAmountEligible($applicationProcess->getAmountRequested());
      }
    }
    elseif (FALSE === $applicationProcess->getIsEligible()) {
      $applicationProcess->setAmountEligible(0.0);
    }
  }

}
