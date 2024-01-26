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

use Civi\Api4\RemoteFundingCase;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingActionLegacy;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

abstract class AbstractNewApplicationFormAction extends AbstractRemoteFundingActionLegacy {

  use NewApplicationFormActionTrait;
  use RemoteFundingActionContactIdRequiredTrait;

  protected FundingCaseTypeManager $_fundingCaseTypeManager;

  protected FundingProgramManager $_fundingProgramManager;

  public function __construct(
    string $actionName,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    CiviEventDispatcherInterface $eventDispatcher,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    parent::__construct(RemoteFundingCase::getEntityName(), $actionName);
    $this->_fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->_fundingProgramManager = $fundingProgramManager;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_relationChecker = $relationChecker;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  protected function createEventParams(int $fundingCaseTypeId, int $fundingProgramId): array {
    Assert::notNull($this->remoteContactId);

    $fundingCaseType = $this->_fundingCaseTypeManager->get($fundingCaseTypeId);
    Assert::notNull(
      $fundingCaseType,
      E::ts('Funding case type with ID "%1" not found', [1 => $fundingCaseTypeId])
    );

    $fundingProgram = $this->_fundingProgramManager->get($fundingProgramId);
    Assert::notNull($fundingProgram, E::ts('Funding program with ID "%1" not found', [1 => $fundingProgramId]));
    $this->assertFundingProgramDates($fundingProgram);
    $this->assertCreateApplicationPermission($fundingProgram);

    return $this->getExtraParams() + [
      'fundingCaseType' => $fundingCaseType,
      'fundingProgram' => $fundingProgram,
    ];
  }

}
