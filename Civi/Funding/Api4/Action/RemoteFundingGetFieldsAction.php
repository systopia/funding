<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingGetFieldsEvent;
use Civi\RemoteTools\Api4\Action\EventGetFieldsAction;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RemoteFundingGetFieldsAction extends EventGetFieldsAction implements RemoteFundingActionInterface {

  use RemoteFundingActionContactIdTrait;

  public function __construct(string $entityName, string $actionName = 'getFields', EventDispatcherInterface $eventDispatcher = NULL) {
    parent::__construct(FundingEvents::REMOTE_REQUEST_INIT_EVENT_NAME,
      FundingEvents::REMOTE_REQUEST_AUTHORIZE_EVENT_NAME,
      $entityName, $actionName, $eventDispatcher);
  }

  /**
   * @noinspection PhpMissingParentCallCommonInspection
   */
  protected function getEventClass(): string {
    return RemoteFundingGetFieldsEvent::class;
  }

}
