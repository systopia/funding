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

namespace Civi\Funding\Api4\Action\Remote\FundingCaseType;

use Civi\Api4\Generic\Result;
use Civi\Api4\RemoteFundingCaseType;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\RemoteFundingActionInterface;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\Remote\FundingCaseType\GetByFundingProgramIdEvent;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\RemoteTools\Api4\Action\AbstractEventAction;
use Civi\RemoteTools\Event\AbstractRequestEvent;

/**
 * @method $this setFundingProgramId(int $fundingProgramId)
 */
final class GetByFundingProgramIdAction extends AbstractEventAction implements RemoteFundingActionInterface {

  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * @var int
   * @required
   */
  protected ?int $fundingProgramId = NULL;

  public function __construct(CiviEventDispatcher $eventDispatcher = NULL) {
    parent::__construct(
      FundingEvents::REQUEST_INIT_EVENT_NAME,
      FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME,
      RemoteFundingCaseType::_getEntityName(),
      'getByFundingProgramId',
      $eventDispatcher
    );
  }

  protected function getEventClass(): string {
    return GetByFundingProgramIdEvent::class;
  }

  protected function updateResult(Result $result, AbstractRequestEvent $event): void {
    /** @var \Civi\Funding\Event\Remote\FundingCaseType\GetByFundingProgramIdEvent $event */
    $result->exchangeArray($event->getRecords());
  }

}
