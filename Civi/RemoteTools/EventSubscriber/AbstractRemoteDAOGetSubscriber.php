<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EventSubscriber;

use Civi\Api4\Generic\DAOGetAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Event\DAOGetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractRemoteDAOGetSubscriber implements EventSubscriberInterface {

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
   * Overwrite in subclasses if necessary
   *
   * @var class-string<\Civi\RemoteTools\Event\DAOGetEvent>
   */
  protected const EVENT_CLASS = DAOGetEvent::class;

  private Api4Interface $api4;

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
  public function onGet(DAOGetEvent $event): void {
    /*
     * Note: "where" could contain excluded fields, i.e. requester could use it
     * to detect values of excluded fields (if he knows the field name). Though
     * we trust the requester that he doesn't misuse it.
     */
    $action = (new DAOGetAction(static::DAO_ENTITY_NAME, $event->getActionName()))
      ->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setLimit($event->getLimit())
      ->setOffset($event->getOffset())
      ->setOrderBy($event->getOrderBy())
      ->setSelect($this->filterSelectFields($event))
      ->setWhere($event->getWhere())
      ->setGroupBy($event->getGroupBy())
      ->setHaving($event->getHaving())
      ->setJoin($event->getJoin());

    $result = $this->api4->executeAction($action);

    $event->setRowCount($result->rowCount)
      ->addDebugOutput(static::class, $result->debug ?? []);
    /** @var array<string, scalar|null> $record */
    foreach ($result as $record) {
      $event->addRecord($this->filterRecordFields($event, $record));
    }
  }

  /**
   * @param \Civi\RemoteTools\Event\DAOGetEvent $event
   *
   * @return string[]
   */
  protected function getExcludedFields(DAOGetEvent $event): array {
    return [];
  }

  /**
   * @param \Civi\RemoteTools\Event\DAOGetEvent $event
   *
   * @return string[]
   */
  protected function getIncludedFields(DAOGetEvent $event): array {
    return ['*'];
  }

  /**
   * @param \Civi\RemoteTools\Event\DAOGetEvent $event
   * @param array<string, scalar|null> $record
   *
   * @return array<string, scalar|null>
   */
  private function filterRecordFields(DAOGetEvent $event, array $record): array {
    $excludedFields = $this->getExcludedFields($event);
    if ([] === $excludedFields) {
      return $record;
    }

    return array_filter($record, function (string $key) use ($excludedFields) {
      return !in_array($key, $excludedFields, TRUE);
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * @param \Civi\RemoteTools\Event\DAOGetEvent $event
   *
   * @return string[]
   */
  private function filterSelectFields(DAOGetEvent $event): array {
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
