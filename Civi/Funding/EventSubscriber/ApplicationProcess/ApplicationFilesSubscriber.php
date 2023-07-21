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

use Civi\Funding\ApplicationProcess\Command\ApplicationFilesAddIdentifiersCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFilesPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandlerInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationFilesSubscriber implements EventSubscriberInterface {

  private ApplicationFilesAddIdentifiersHandlerInterface $filesAddIdentifiersHandler;

  private ApplicationFilesPersistHandlerInterface $filesPersistHandler;

  /**
   * @phpstan-var array<string, \Civi\Funding\Entity\ExternalFileEntity>
   */
  private array $files = [];

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
      ApplicationFormSubmitSuccessEvent::class => 'onSubmitSuccess',
    ];
  }

  public function __construct(
    ApplicationFilesAddIdentifiersHandlerInterface $filesAddIdentifiersHandler,
    ApplicationFilesPersistHandlerInterface $filesPersistHandler
  ) {
    $this->filesAddIdentifiersHandler = $filesAddIdentifiersHandler;
    $this->filesPersistHandler = $filesPersistHandler;
  }

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    $requestData = $this->filesAddIdentifiersHandler->handle(new ApplicationFilesAddIdentifiersCommand(
      $event->getApplicationProcessBundle(),
    ));

    $event->getApplicationProcess()->setRequestData($requestData);
  }

  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $this->files = $this->filesPersistHandler->handle(new ApplicationFilesPersistCommand(
      $event->getApplicationProcessBundle(),
      NULL,
    ));
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    $requestData = $this->filesAddIdentifiersHandler->handle(new ApplicationFilesAddIdentifiersCommand(
      $event->getApplicationProcessBundle(),
    ));

    $event->getApplicationProcess()->setRequestData($requestData);
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $this->files = $this->filesPersistHandler->handle(new ApplicationFilesPersistCommand(
      $event->getApplicationProcessBundle(),
      $event->getPreviousApplicationProcess(),
    ));
  }

  public function onSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    $event->getResult()->setFiles($this->files);
  }

}
