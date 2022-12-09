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
use CRM_Funding_ExtensionUtil as E;
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
  protected ?int $fundingProgramId = NULL;

  /**
   * @var int
   * @required
   */
  protected ?int $fundingCaseTypeId = NULL;

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
    $this->assertFundingCaseTypeAndProgramRelated($this->getFundingCaseTypeId(), $this->getFundingProgramId());
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    if (NULL === $event->getJsonSchema() || NULL === $event->getUiSchema()) {
      throw new \API_Exception(E::ts('Invalid funding program ID or funding case type ID'), 'invalid_arguments');
    }

    Assert::keyExists($event->getData(), 'fundingCaseTypeId');
    Assert::same($event->getData()['fundingCaseTypeId'], $this->getFundingCaseTypeId());
    Assert::keyExists($event->getData(), 'fundingProgramId');
    Assert::same($event->getData()['fundingProgramId'], $this->getFundingProgramId());

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
      $this->createEventParams($this->getFundingCaseTypeId(), $this->getFundingProgramId()),
    );
  }

  private function getFundingCaseTypeId(): int {
    Assert::notNull($this->fundingCaseTypeId);

    return $this->fundingCaseTypeId;
  }

  private function getFundingProgramId(): int {
    Assert::notNull($this->fundingProgramId);

    return $this->fundingProgramId;
  }

}
