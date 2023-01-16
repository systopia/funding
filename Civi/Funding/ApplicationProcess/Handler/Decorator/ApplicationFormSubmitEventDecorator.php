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

namespace Civi\Funding\ApplicationProcess\Handler\Decorator;

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Webmozart\Assert\Assert;

final class ApplicationFormSubmitEventDecorator implements ApplicationFormSubmitHandlerInterface {

  private ApplicationFormSubmitHandlerInterface $handler;

  private CiviEventDispatcher $eventDispatcher;

  public function __construct(ApplicationFormSubmitHandlerInterface $handler, CiviEventDispatcher $eventDispatcher) {
    $this->handler = $handler;
    $this->eventDispatcher = $eventDispatcher;
  }

  public function handle(ApplicationFormSubmitCommand $command): ApplicationFormSubmitResult {
    $result = $this->handler->handle($command);
    if ($result->isSuccess()) {
      Assert::notNull($result->getValidatedData());
      $event = new ApplicationFormSubmitSuccessEvent(
        $command->getContactId(),
        $command->getApplicationProcessBundle(),
        $command->getData(),
        $result->getValidatedData(),
      );

      $this->eventDispatcher->dispatch(ApplicationFormSubmitSuccessEvent::class, $event);
    }

    return $result;
  }

}
