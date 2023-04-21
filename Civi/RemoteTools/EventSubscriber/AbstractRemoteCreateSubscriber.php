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

namespace Civi\RemoteTools\EventSubscriber;

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Event\CreateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractRemoteCreateSubscriber implements EventSubscriberInterface {

  /**
   * Overwrite in subclasses
   *
   * @var string
   */
  protected const BASIC_ENTITY_NAME = NULL;

  /**
   * Overwrite in subclasses
   *
   * @var string
   */
  protected const ENTITY_NAME = NULL;

  /**
   * Overwrite in subclasses if necessary
   *
   * @phpstan-var class-string<\Civi\RemoteTools\Event\CreateEvent>
   */
  protected const EVENT_CLASS = CreateEvent::class;

  protected Api4Interface $api4;

  public static function getSubscribedEvents(): array {
    return [self::getEventName() => 'onCreate'];
  }

  private static function getEventName(): string {
    return (static::EVENT_CLASS)::getEventName(static::ENTITY_NAME);
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreate(CreateEvent $event): void {
    $result = $this->api4->executeAction($this->createAction($event));

    $event
      ->setRowCount($result->count())
      ->addDebugOutput(static::class, $result->debug ?? [])
      // @phpstan-ignore-next-line
      ->setRecords($result->getArrayCopy());
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  protected function createAction(CreateEvent $event): AbstractAction {
    /** @var \Civi\Api4\Generic\AbstractCreateAction $action */
    $action = $this->api4->createAction(static::BASIC_ENTITY_NAME, 'create');

    return $action->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setValues($event->getValues());
  }

}
