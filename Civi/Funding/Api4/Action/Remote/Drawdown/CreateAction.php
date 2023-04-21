<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\Remote\Drawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\Result;
use Civi\Api4\RemoteFundingDrawdown;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\RemoteTools\Event\CreateEvent;
use Webmozart\Assert\Assert;

/**
 * @method $this setAmount(float $amount)
 * @method $this setPayoutProcessId(int $payoutProcessId)
 */
final class CreateAction extends AbstractRemoteFundingAction {

  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * @var int
   * @required
   */
  protected ?int $payoutProcessId = NULL;

  /**
   * @var mixed
   * @required
   * @phpstan-ignore-next-line CiviCRM (v5.59) does not know float/double in @var.
   */
  protected ?float $amount = NULL;

  public function __construct(CiviEventDispatcherInterface $eventDispatcher) {
    parent::__construct(RemoteFundingDrawdown::_getEntityName(), 'create');
    $this->_eventDispatcher = $eventDispatcher;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    $result->setCountMatched($event->getRowCount());
    $result->exchangeArray($event->getRecords());
  }

  private function createEvent(): CreateEvent {
    Assert::notNull($this->payoutProcessId);
    Assert::notNull($this->amount);

    return new CreateEvent(
      FundingDrawdown::_getEntityName(),
      'create',
      [
        'values' => DrawdownEntity::fromArray([
          'payout_process_id' => $this->payoutProcessId,
          'status' => 'new',
          'creation_date' => date('Y-m-d H:i:s'),
          'amount' => $this->amount,
          'acception_date' => NULL,
          'requester_contact_id' => $this->getContactId(),
          'reviewer_contact_id' => NULL,
        ])->toArray(),
      ],
    );
  }

}
