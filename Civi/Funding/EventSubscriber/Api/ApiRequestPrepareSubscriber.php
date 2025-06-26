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

namespace Civi\Funding\EventSubscriber\Api;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiRequestPrepareSubscriber implements EventSubscriberInterface {

  public const CONTEXT_KEY_ACTION_NAME = 'apiActionName';

  public const CONTEXT_KEY_ENTITY_NAME = 'apiEntityName';

  private RequestContextInterface $requestContext;

  public function __construct(RequestContextInterface $requestContext) {
    $this->requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return ['civi.api.prepare' => ['onApiPrepare', PHP_INT_MAX]];
  }

  public function onApiPrepare(PrepareEvent $event): void {
    $request = $event->getApiRequest();
    if ($request instanceof AbstractAction) {
      $this->requestContext->set(self::CONTEXT_KEY_ENTITY_NAME, $request->getEntityName());
      $this->requestContext->set(self::CONTEXT_KEY_ACTION_NAME, $request->getActionName());
    }
  }

}
