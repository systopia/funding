<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Api4\Action\RemoteDAOGetFieldsAction;
use Civi\Api4\Generic\Api4Interface;
use Civi\Funding\Event\RemoteGetFieldsEvent;
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

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public static function getSubscribedEvents(): array {
    return [RemoteGetFieldsEvent::getEventName(static::ENTITY_NAME) => 'onGetFields'];
  }

  public function onGetFields(RemoteGetFieldsEvent $event): void {
    $action = (new RemoteDAOGetFieldsAction(static::DAO_ENTITY_NAME, $event->getActionName()))
      ->setCheckPermissions($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setAction($event->getAction())
      ->setLoadOptions($event->getLoadOptions())
      ->setValues($event->getValues())
      ->setIncludedFields($this->getIncludedFields($event))
      ->setExcludedFields($this->getExcludedFields($event));

    $result = $this->api4->executeAction($action);
    $event->setFields($result->getArrayCopy())
      ->addDebugOutput(static::class, $result->debug ?? []);
  }

  protected function getExcludedFields(RemoteGetFieldsEvent $event): array {
    return [];
  }

  protected function getIncludedFields(RemoteGetFieldsEvent $event): ?array {
    return NULL;
  }

}
