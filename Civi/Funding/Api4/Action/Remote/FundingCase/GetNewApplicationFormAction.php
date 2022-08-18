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

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @method $this setFundingProgramId(int $fundingProgramId)
 * @method $this setFundingCaseTypeId(int $fundingCaseTypeId)
 */
final class GetNewApplicationFormAction extends AbstractNewApplicationFormAction {

  use NewApplicationFormActionTrait;

  /**
   * @var int
   * @required
   */
  protected int $fundingProgramId;

  /**
   * @var int
   * @required
   */
  protected int $fundingCaseTypeId;

  public function __construct(
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager,
    CiviEventDispatcher $eventDispatcher,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    parent::__construct(
      'getNewApplicationForm',
      $remoteFundingEntityManager,
      $eventDispatcher,
      $relationChecker,
    );
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $this->assertFundingCaseTypeAndProgramRelated($this->fundingCaseTypeId, $this->fundingProgramId);
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    if (NULL === $event->getJsonSchema() || NULL === $event->getUiSchema()) {
      throw new \API_Exception('Invalid fundingProgramId or fundingCaseTypeId', 'invalid_arguments');
    }

    Assert::keyExists($event->getData(), 'fundingCaseTypeId');
    Assert::same($event->getData()['fundingCaseTypeId'], $this->fundingCaseTypeId);
    Assert::keyExists($event->getData(), 'fundingProgramId');
    Assert::same($event->getData()['fundingProgramId'], $this->fundingProgramId);

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
  private function createEvent(): GetNewApplicationFormEvent {
    return GetNewApplicationFormEvent::fromApiRequest(
      $this,
      $this->createEventParams($this->fundingCaseTypeId, $this->fundingProgramId),
    );
  }

}
