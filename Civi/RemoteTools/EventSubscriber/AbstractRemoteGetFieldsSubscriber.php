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
use Civi\RemoteTools\Event\GetFieldsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-type FieldT array<string, array<string, scalar>|scalar[]|scalar|null>&array{name: string}
 */
abstract class AbstractRemoteGetFieldsSubscriber implements EventSubscriberInterface {

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

  protected static function getEventName(): string {
    return (static::EVENT_CLASS)::getEventName(static::ENTITY_NAME);
  }

  /**
   * @throws \API_Exception
   */
  public function onGetFields(GetFieldsEvent $event): void {
    /** @var iterable<FieldT> $result */
    $result = $this->api4->executeAction($this->createAction($event));
    $fields = $this->filterFields($result, $event);
    $event->setFields($fields)
      ->addDebugOutput(static::class, $result->debug ?? []);
  }

  /**
   * @throws \Civi\API\Exception\NotImplementedException
   */
  protected function createAction(GetFieldsEvent $event): AbstractAction {
    /** @var \Civi\Api4\Generic\BasicGetFieldsAction $action */
    $action = $this->api4->createAction(static::BASIC_ENTITY_NAME, 'getFields');

    return $action->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setAction($event->getAction())
      ->setLoadOptions($event->getLoadOptions())
      ->setValues($event->getValues());
  }

  /**
   * @return array<string>
   */
  protected function getAllowedFieldKeys(GetFieldsEvent $event): array {
    return [
      'name',
      'options',
      'readonly',
      'operators',
      'data_type',
      'nullable',
      'description',
    ];
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

  /**
   * @param iterable<FieldT> $fields
   * @param \Civi\RemoteTools\Event\GetFieldsEvent $event
   *
   * @return array<FieldT>
   */
  private function filterFields(iterable $fields, GetFieldsEvent $event): array {
    $includedFields = $this->getIncludedFields($event);
    $excludedFields = $this->getExcludedFields($event);
    $allowedFieldKeys = $this->getAllowedFieldKeys($event);

    $filteredFields = [];
    foreach ($fields as $field) {
      if (!$this->isFieldInResult($field['name'], $includedFields, $excludedFields)) {
        continue;
      }

      if ($this->isFieldSerialized($field)) {
        $field['data_type'] = 'Array';
      }
      /** @phpstan-var FieldT $field */
      $field = array_filter(
        $field,
        fn ($name) => in_array($name, $allowedFieldKeys, TRUE),
        ARRAY_FILTER_USE_KEY
      );

      $filteredFields[] = $field;
    }

    return $filteredFields;
  }

  /**
   * @param string $fieldName
   * @param array<string>|null $includedFields
   * @param array<string> $excludedFields
   *
   */
  private function isFieldInResult(string $fieldName, ?array $includedFields, array $excludedFields): bool {
    if (NULL !== $includedFields) {
      return in_array($fieldName, $includedFields, TRUE);
    }

    return !in_array($fieldName, $excludedFields, TRUE);
  }

  /**
   * @phpstan-param FieldT $field
   */
  private function isFieldSerialized(array $field): bool {
    return 0 !== ($field['serialize'] ?? 0);
  }

}
