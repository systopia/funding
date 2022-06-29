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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingApplicationProcessGetFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManager;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @method void setApplicationProcessId(int $applicationProcessId)
 */
final class GetFormAction extends AbstractRemoteFundingAction {

  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * @var int
   * @required
   */
  protected int $applicationProcessId;

  private RemoteFundingEntityManagerInterface $_remoteFundingEntityManager;

  public function __construct(
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager = NULL,
    CiviEventDispatcher $eventDispatcher = NULL
  ) {
    parent::__construct('RemoteFundingApplicationProcess', 'getForm');
    $this->_remoteFundingEntityManager = $remoteFundingEntityManager ?? RemoteFundingEntityManager::getInstance();
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
    if ([] === $event->getJsonSchema() || [] === $event->getUiSchema()) {
      throw new \API_Exception('Invalid applicationProcessId', 'invalid_arguments');
    }

    Assert::keyExists($event->getData(), 'applicationProcessId');
    Assert::same($event->getData()['applicationProcessId'], $this->applicationProcessId);

    $result->rowCount = 1;
    $result->exchangeArray([
      'jsonSchema' => $event->getJsonSchema(),
      'uiSchema' => $event->getUiSchema(),
      'data' => $event->getData(),
    ]);
  }

  /**
   * @throws \API_Exception
   */
  private function createEvent(): RemoteFundingApplicationProcessGetFormEvent {
    Assert::notNull($this->remoteContactId);
    /** @var array<string, mixed>&array{id: int, funding_case_id: int} $applicationProcess */
    $applicationProcess = $this->_remoteFundingEntityManager->getById(
      'FundingApplicationProcess', $this->applicationProcessId, $this->remoteContactId
    );
    /** @var array<string, mixed>&array{id: int, funding_case_type_id: int} $fundingCase */
    $fundingCase = $this->_remoteFundingEntityManager->getById(
      'FundingCase', $applicationProcess['funding_case_id'], $this->remoteContactId
    );
    $fundingCaseType = $this->_remoteFundingEntityManager->getById(
      'FundingCaseType', $fundingCase['funding_case_type_id'], $this->remoteContactId
    );

    return RemoteFundingApplicationProcessGetFormEvent::fromApiRequest($this, $this->getExtraParams() + [
      'applicationProcess' => $applicationProcess,
      'fundingCase' => $fundingCase,
      'fundingCaseType' => $fundingCaseType,
    ]);
  }

}
