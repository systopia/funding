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
use Civi\RemoteTools\Event\GetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractRemoteGetSubscriber implements EventSubscriberInterface {

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
   * @phpstan-var class-string<\Civi\RemoteTools\Event\GetEvent>
   */
  protected const EVENT_CLASS = GetEvent::class;

  protected Api4Interface $api4;

  public static function getSubscribedEvents(): array {
    return [self::getEventName() => 'onGet'];
  }

  private static function getEventName(): string {
    return (static::EVENT_CLASS)::getEventName(static::ENTITY_NAME);
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \API_Exception
   */
  public function onGet(GetEvent $event): void {
    $result = $this->api4->executeAction($this->createAction($event));

    $event->setRowCount($result->rowCount ?? $result->count())
      ->addDebugOutput(static::class, $result->debug ?? []);
    /** @var array<string, scalar|null> $record */
    foreach ($result as $record) {
      $event->addRecord($this->filterRecordFields($event, $record));
    }
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  protected function createAction(GetEvent $event): AbstractAction {
    /*
     * Note: "where" could contain excluded fields, i.e. requesters could use it
     * to detect values of excluded fields (if they know the field name). Though
     * we trust the requesters that they don't misuse it.
     */
    /** @var \Civi\Api4\Generic\AbstractGetAction $action */
    $action = $this->api4->createAction(static::BASIC_ENTITY_NAME, 'get');

    return $action->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setLimit($event->getLimit())
      ->setOffset($event->getOffset())
      ->setOrderBy($event->getOrderBy())
      ->setSelect($this->filterSelectFields($event))
      ->setWhere($event->getWhere());
  }

  /**
   * @return string[]
   *   Field names that will be excluded from records.
   */
  protected function getExcludedFields(GetEvent $event): array {
    return [];
  }

  /**
   * @return string[]
   *   Field names that are allowed to be included in records. "*" means all
   *   fields.
   */
  protected function getIncludedFields(GetEvent $event): array {
    return ['*'];
  }

  /**
   * @phpstan-param array<string, scalar|null> $record
   *
   * @phpstan-return array<string, scalar|null>
   */
  private function filterRecordFields(GetEvent $event, array $record): array {
    $excludedFields = $this->getExcludedFields($event);
    if ([] === $excludedFields) {
      return $record;
    }

    return array_filter($record, function (string $key) use ($excludedFields) {
      return !in_array($key, $excludedFields, TRUE);
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * @return string[]
   */
  private function filterSelectFields(GetEvent $event): array {
    $includedFields = $this->getIncludedFields($event);
    if (in_array('*', $includedFields, TRUE)) {
      return $event->getSelect();
    }

    if ([] === $event->getSelect() || in_array('*', $event->getSelect(), TRUE)) {
      return $includedFields;
    }

    $select = array_intersect($event->getSelect(), $includedFields + ['row_count']);

    return [] === $select ? $includedFields : $select;
  }

}
