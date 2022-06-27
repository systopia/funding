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

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;

/**
 * @method void setId(int $id)
 * @method void setType(string $type)
 */
final class RemoteFundingProgramGetRelatedAction extends AbstractAction implements RemoteFundingActionInterface {

  use EventActionTrait;

  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * Must be initialized because it is directly accessed in AbstractAction.
   *
   * @var int|null
   * @required
   */
  protected ?int $id = NULL;

  /**
   * Must be initialized because it is directly accessed in AbstractAction.
   *
   * @var string|null
   * @required
   */
  protected ?string $type = NULL;

  public function __construct(CiviEventDispatcher $eventDispatcher = NULL) {
    parent::__construct('RemoteFundingProgram', 'getRelated');
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
    $this->_authorizeRequestEventName = FundingEvents::REMOTE_REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REMOTE_REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $event = $this->createEvent();

    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->rowCount = $event->getRowCount();
    $result->exchangeArray($event->getRecords());
  }

  /**
   * @return \Civi\Funding\Event\RemoteFundingDAOGetEvent
   *
   * @throws \API_Exception
   */
  private function createEvent(): RemoteFundingDAOGetEvent {
    $event = RemoteFundingDAOGetEvent::fromApiRequest($this, $this->getExtraParams());
    $event->addJoin('FundingProgramRelationship AS relationship', 'INNER', NULL,
      ['id', '=', 'relationship.id_b'],
      ['relationship.type', '=', "'" . $this->type . "'"]);
    $event->addWhere('relationship.id_a', '=', $this->id);

    return $event;
  }

}
