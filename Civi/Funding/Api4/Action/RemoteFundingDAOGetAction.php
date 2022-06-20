<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Civi\RemoteTools\Api4\Action\EventDAOGetAction;

class RemoteFundingDAOGetAction extends EventDAOGetAction implements RemoteFundingActionInterface {

  use RemoteFundingActionContactIdRequiredTrait;

  public function __construct(string $entityName, string $actionName = 'get',
    CiviEventDispatcher $eventDispatcher = NULL
  ) {
    parent::__construct(FundingEvents::REMOTE_REQUEST_INIT_EVENT_NAME,
      FundingEvents::REMOTE_REQUEST_AUTHORIZE_EVENT_NAME,
      $entityName, $actionName, $eventDispatcher);
  }

  /**
   * @noinspection PhpMissingParentCallCommonInspection
   */
  protected function getEventClass(): string {
    return RemoteFundingDAOGetEvent::class;
  }

}
