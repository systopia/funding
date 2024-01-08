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

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormNewSubmitResult;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Webmozart\Assert\Assert;

final class ApplicationFormNewSubmitEventDecorator implements ApplicationFormNewSubmitHandlerInterface {

  private ApplicationFormNewSubmitHandlerInterface $handler;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(
    ApplicationFormNewSubmitHandlerInterface $handler,
    CiviEventDispatcherInterface $eventDispatcher
  ) {
    $this->handler = $handler;
    $this->eventDispatcher = $eventDispatcher;
  }

  public function handle(ApplicationFormNewSubmitCommand $command): ApplicationFormNewSubmitResult {
    $result = $this->handler->handle($command);
    if ($result->isSuccess()) {
      Assert::notNull($result->getApplicationProcessBundle());
      $event = new ApplicationFormSubmitSuccessEvent(
        $command->getContactId(),
        $result->getApplicationProcessBundle(),
        $command->getData(),
        $result,
        NULL,
      );

      $this->eventDispatcher->dispatch(ApplicationFormSubmitSuccessEvent::class, $event);
    }

    return $result;
  }

}
