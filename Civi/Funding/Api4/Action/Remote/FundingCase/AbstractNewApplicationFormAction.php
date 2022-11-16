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
use Civi\Api4\RemoteFundingCase;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type fundingCaseTypeT array{
 *   id: int,
 *   abbreviation: string,
 *   title: string,
 *   name: string,
 *   properties: array<string, mixed>
 * }
 *
 * @phpstan-type fundingProgramT array{
 *   id: int,
 *   title: string,
 *   abbreviation: string,
 *   start_date: string,
 *   end_date: string,
 *   requests_start_date: string,
 *   requests_end_date: string,
 *   currency: string,
 *   budget: float|null,
 * }
 */
abstract class AbstractNewApplicationFormAction extends AbstractRemoteFundingAction {

  use NewApplicationFormActionTrait;
  use RemoteFundingActionContactIdRequiredTrait;

  protected RemoteFundingEntityManagerInterface $_remoteFundingEntityManager;

  public function __construct(
    string $actionName,
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager,
    CiviEventDispatcher $eventDispatcher,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    parent::__construct(RemoteFundingCase::_getEntityName(), $actionName);
    $this->_remoteFundingEntityManager = $remoteFundingEntityManager;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_relationChecker = $relationChecker;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \API_Exception
   */
  protected function createEventParams(int $fundingCaseTypeId, int $fundingProgramId): array {
    Assert::notNull($this->remoteContactId);

    /** @phpstan-var fundingCaseTypeT|null $fundingCaseTypeValues */
    $fundingCaseTypeValues = $this->_remoteFundingEntityManager->getById(
      FundingCaseType::_getEntityName(),
      $fundingCaseTypeId,
      $this->remoteContactId,
    );
    Assert::notNull($fundingCaseTypeValues, sprintf('Funding case type with ID %d not found', $fundingCaseTypeId));
    $fundingCaseType = FundingCaseTypeEntity::fromArray($fundingCaseTypeValues);

    /** @var fundingProgramT|null $fundingProgramValues */
    $fundingProgramValues = $this->_remoteFundingEntityManager->getById(
      FundingProgram::_getEntityName(),
      $fundingProgramId,
      $this->remoteContactId,
    );
    Assert::notNull($fundingProgramValues, sprintf('Funding program with ID %d not found', $fundingProgramId));
    $fundingProgram = FundingProgramEntity::fromArray($fundingProgramValues);
    $this->assertFundingProgramDates($fundingProgram);
    $this->assertCreateApplicationPermission($fundingProgram);

    return $this->getExtraParams() + [
      'fundingCaseType' => $fundingCaseType,
      'fundingProgram' => $fundingProgram,
    ];
  }

}
