<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Event;

use Civi\API\Event\RequestTrait;
use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\EventActionInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Webmozart\Assert\Assert;

/**
 * @method \Civi\Api4\Generic\AbstractAction&\Civi\RemoteTools\Api4\Action\EventActionInterface getApiRequest()
 *
 * @phpstan-consistent-constructor
 */
abstract class AbstractApiRequestEvent extends Event {

  use RequestTrait;

  public static function fromApiRequest(AbstractAction $apiRequest): self {
    return new static($apiRequest);
  }

  public function __construct(AbstractAction $apiRequest) {
    Assert::isInstanceOf($apiRequest, EventActionInterface::class);
    $this->apiRequest = $apiRequest;
  }

}
