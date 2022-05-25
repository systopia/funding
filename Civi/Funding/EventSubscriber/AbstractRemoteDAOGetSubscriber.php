<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Api4\Generic\Api4Interface;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Funding\Event\RemoteGetEvent;
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

  private Api4Interface $api4;

  public static function getSubscribedEvents(): array {
    return [RemoteGetEvent::getEventName(static::ENTITY_NAME) => 'onGet'];
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function onGet(RemoteGetEvent $event): void {
    // TODO: "where" could contain excluded fields
    $action = (new DAOGetAction(static::DAO_ENTITY_NAME, $event->getActionName()))
      ->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setLimit($event->getLimit())
      ->setOffset($event->getOffset())
      ->setOrderBy($event->getOrderBy())
      ->setSelect($this->filterSelectFields($event))
      ->setWhere($event->getWhere());

    $result = $this->api4->executeAction($action);

    $event->setRowCount($result->rowCount)
      ->addDebugOutput(static::class, $result->debug);
    foreach ($result as $record) {
      $event->addRecord($this->filterRecordFields($event, $record));
    }
  }

  protected function getExcludedFields(RemoteGetEvent $event): array {
    return [];
  }

  protected function getIncludedFields(RemoteGetEvent $event): array {
    return ['*'];
  }

  private function filterRecordFields(RemoteGetEvent $event, array $record): array {
    $excludedFields = $this->getExcludedFields($event);
    if ([] === $excludedFields) {
      return $record;
    }

    return array_filter($record, function (string $key) use ($excludedFields) {
      return !in_array($key, $excludedFields, TRUE);
    }, ARRAY_FILTER_USE_KEY);
  }

  private function filterSelectFields(RemoteGetEvent $event): array {
    $includedFields = $this->getIncludedFields($event);
    if (in_array('*', $includedFields, TRUE)) {
      return $event->getSelect();
    }

    if ([] === $event->getSelect() || in_array('*', $event->getSelect(), TRUE)) {
      return $includedFields;
    }

    return array_intersect($event->getSelect(), $includedFields + ['row_count']) ?: $includedFields;
  }

}
