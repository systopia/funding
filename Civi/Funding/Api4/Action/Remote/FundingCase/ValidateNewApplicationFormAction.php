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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\Api4\FundingCaseType;
use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 */
final class ValidateNewApplicationFormAction extends AbstractRemoteFundingAction {

  use NewApplicationFormActionTrait;
  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * @var array<string, mixed>
   * @required
   */
  protected array $data;

  private RemoteFundingEntityManagerInterface $_remoteFundingEntityManager;

  public function __construct(
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager,
    CiviEventDispatcher $eventDispatcher,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    parent::__construct('RemoteFundingCase', 'validateNewApplicationForm');
    $this->_remoteFundingEntityManager = $remoteFundingEntityManager;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_relationChecker = $relationChecker;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $this->assertFundingCaseTypeAndProgramRelated($this->getFundingCaseTypeId(), $this->getFundingProgramId());
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();

    if (NULL === $event->isValid()) {
      throw new \API_Exception('Form not validated');
    }

    $result->rowCount = 1;
    $result->exchangeArray([
      'valid' => $event->isValid(),
      'errors' => $event->getErrors(),
    ]);
  }

  /**
   * @throws \API_Exception
   */
  private function createEvent(): ValidateNewApplicationFormEvent {
    Assert::notNull($this->remoteContactId);
    $fundingCaseType = $this->_remoteFundingEntityManager->getById(
      FundingCaseType::_getEntityName(),
      $this->getFundingCaseTypeId(),
      $this->remoteContactId,
      $this->getContactId()
    );
    /** @var array<string, mixed>&array{requests_start_date: string|null, requests_end_date: string|null} $fundingProgram */
    $fundingProgram = $this->_remoteFundingEntityManager->getById(
      FundingProgram::_getEntityName(),
      $this->getFundingProgramId(),
      $this->remoteContactId,
      $this->getContactId()
    );

    $this->assertFundingProgramDates($fundingProgram);

    return ValidateNewApplicationFormEvent::fromApiRequest(
      $this,
      $this->getExtraParams() + [
        'fundingCaseType' => $fundingCaseType,
        'fundingProgram' => $fundingProgram,
      ]
    );
  }

  public function getFundingProgramId(): int {
    Assert::keyExists($this->data, 'fundingProgramId');
    Assert::integer($this->data['fundingProgramId']);

    return $this->data['fundingProgramId'];
  }

  public function getFundingCaseTypeId(): int {
    Assert::keyExists($this->data, 'fundingCaseTypeId');
    Assert::integer($this->data['fundingCaseTypeId']);

    return $this->data['fundingCaseTypeId'];
  }

}
