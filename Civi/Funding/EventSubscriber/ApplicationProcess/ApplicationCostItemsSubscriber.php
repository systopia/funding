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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationCostItemsSubscriber implements EventSubscriberInterface {

  private ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
    ];
  }

  public function __construct(
    ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler
  ) {
    $this->costItemsPersistHandler = $costItemsPersistHandler;
  }

  public function onFormSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    if ($event->getResult()->getValidationResult()->isReadOnly() ||
      NULL !== $event->getApplicationProcess()->getRestoredSnapshot()) {
      return;
    }

    $this->costItemsPersistHandler->handle(new ApplicationCostItemsPersistCommand(
      $event->getApplicationProcessBundle(),
      $event->getValidatedData()->getCostItemsData()
    ));
  }

}
