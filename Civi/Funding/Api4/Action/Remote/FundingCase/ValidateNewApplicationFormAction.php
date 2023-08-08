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
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Traits\FundingCaseTypeIdParameterTrait;
use Civi\Funding\Api4\Action\Traits\FundingProgramIdParameterTrait;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\Api4\Action\Traits\DataParameterTrait;

/**
 * @method $this setData(array $data)
 */
class ValidateNewApplicationFormAction extends AbstractNewApplicationFormAction {

  use DataParameterTrait;

  use FundingCaseTypeIdParameterTrait;

  use FundingProgramIdParameterTrait;

  public function __construct(
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    CiviEventDispatcherInterface $eventDispatcher,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    parent::__construct(
      'validateNewApplicationForm',
      $fundingCaseTypeManager,
      $fundingProgramManager,
      $eventDispatcher,
      $relationChecker,
    );
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $this->assertFundingCaseTypeAndProgramRelated($this->getFundingCaseTypeId(), $this->getFundingProgramId());
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();

    if (NULL === $event->isValid()) {
      throw new FundingException('Form not validated');
    }

    $result->rowCount = 1;
    $result->exchangeArray([
      'valid' => $event->isValid(),
      'errors' => $event->getErrors(),
    ]);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createEvent(): ValidateNewApplicationFormEvent {
    return ValidateNewApplicationFormEvent::fromApiRequest(
      $this,
      $this->createEventParams($this->getFundingCaseTypeId(), $this->getFundingProgramId()),
    );
  }

}
