<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\EventSubscriber;

use Civi\Api4\FundingCase;
use Civi\Funding\Event\PayoutProcess\DrawdownUpdatedEvent;
use Civi\Funding\EventSubscriber\Api\ApiRequestPrepareSubscriber;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\HiHConstants;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Final drawdowns shall be in status "new" instead of "accepted".
 */
final class HiHDrawdownSubscriber implements EventSubscriberInterface {

  private RequestContextInterface $requestContext;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [DrawdownUpdatedEvent::class => ['onUpdated', -1]];
  }

  public function __construct(RequestContextInterface $requestContext) {
    $this->requestContext = $requestContext;
  }

  public function onUpdated(DrawdownUpdatedEvent $event): void {
    if (HiHConstants::FUNDING_CASE_TYPE_NAME === $event->getFundingCaseType()->getName()
      && $this->isFinishClearingRequest()) {
      if ('accepted' === $event->getDrawdown()->getStatus()) {
        $event->getDrawdown()
          ->setStatus('new')
          ->setAcceptionDate(NULL)
          ->setReviewerContactId(NULL);
      }
    }
  }

  private function isFinishClearingRequest(): bool {
    return FundingCase::getEntityName() === $this->requestContext->get(
      ApiRequestPrepareSubscriber::CONTEXT_KEY_ENTITY_NAME
      ) && 'finishClearing' === $this->requestContext->get(ApiRequestPrepareSubscriber::CONTEXT_KEY_ACTION_NAME);
  }

}
