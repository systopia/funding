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

use Civi\RemoteTools\Api4\Action\RemoteDAOGetFieldsAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Event\GetFieldsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractRemoteDAOGetFieldsSubscriber implements EventSubscriberInterface {

  /**
   * Overwrite in subclasses
   *
   * @var string
   */
  protected const DAO_ENTITY_NAME = NULL;

  /**
   * Overwrite in subclasses
   *
   * @var string
   */
  protected const ENTITY_NAME = NULL;

  /**
   * Overwrite in subclasses, if necessary
   *
   * @var class-string<\Civi\RemoteTools\Event\GetFieldsEvent>
   */
  protected const EVENT_CLASS = GetFieldsEvent::class;

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public static function getSubscribedEvents(): array {
    return [self::getEventName() => 'onGetFields'];
  }

  private static function getEventName(): string {
    return (static::EVENT_CLASS)::getEventName(static::ENTITY_NAME);
  }

  /**
   * @throws \API_Exception
   */
  public function onGetFields(GetFieldsEvent $event): void {
    $action = (new RemoteDAOGetFieldsAction(static::DAO_ENTITY_NAME, $event->getActionName()))
      ->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setAction($event->getAction())
      ->setLoadOptions($event->getLoadOptions())
      ->setValues($event->getValues())
      ->setIncludedFields($this->getIncludedFields($event))
      ->setExcludedFields($this->getExcludedFields($event));

    $result = $this->api4->executeAction($action);
    /** @var array<array<string, array<string, scalar>|scalar[]|scalar|null>> $fields */
    $fields = $result->getArrayCopy();
    $event->setFields($fields)
      ->addDebugOutput(static::class, $result->debug ?? []);
  }

  /**
   * @param \Civi\RemoteTools\Event\GetFieldsEvent $event
   *
   * @return string[]
   */
  protected function getExcludedFields(GetFieldsEvent $event): array {
    return [];
  }

  /**
   * @param \Civi\RemoteTools\Event\GetFieldsEvent $event
   *
   * @return string[]|null
   */
  protected function getIncludedFields(GetFieldsEvent $event): ?array {
    return NULL;
  }

}
