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

use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationResourcesItemsSubscriber implements EventSubscriberInterface {

  private ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
    ];
  }

  public function __construct(ApplicationResourcesItemsPersistHandlerInterface $resourcesItemsPersistHandler) {
    $this->resourcesItemsPersistHandler = $resourcesItemsPersistHandler;
  }

  public function onFormSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    if ($event->getResult()->getValidationResult()->isReadOnly() ||
      NULL !== $event->getApplicationProcess()->getRestoredSnapshot() ||
      'delete' === $event->getAction()) {
      return;
    }

    $this->resourcesItemsPersistHandler->handle(new ApplicationResourcesItemsPersistCommand(
      $event->getApplicationProcessBundle(),
      $event->getValidatedData()->getResourcesItemsData()
    ));
  }

}
