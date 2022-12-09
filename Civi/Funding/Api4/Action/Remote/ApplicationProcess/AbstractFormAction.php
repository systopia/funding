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

use Civi\Api4\FundingCase;
use Civi\Api4\FundingCaseType;
use Civi\Api4\FundingProgram;
use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type applicationProcessT array{
 *   id: int,
 *   identifier: string,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   request_data: array<string, mixed>,
 *   amount_requested: float,
 *   amount_granted: float|null,
 *   granted_budget: float|null,
 *   is_review_content: bool|null,
 *   reviewer_cont_contact_id: int|null,
 *   is_review_calculative: bool|null,
 *   reviewer_calc_contact_id: int|null,
 * }
 *
 * @phpstan-type fundingCaseT array{
 *   id: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   recipient_contact_id: int,
 *   creation_date: string,
 *   modification_date: string,
 * }
 *
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
abstract class AbstractFormAction extends AbstractRemoteFundingAction {

  use RemoteFundingActionContactIdRequiredTrait;

  protected RemoteFundingEntityManagerInterface $_remoteFundingEntityManager;

  public function __construct(
    string $actionName,
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager,
    CiviEventDispatcher $eventDispatcher
  ) {
    parent::__construct(RemoteFundingApplicationProcess::_getEntityName(), $actionName);
    $this->_remoteFundingEntityManager = $remoteFundingEntityManager;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \API_Exception
   */
  protected function createEventParams(int $applicationProcessId): array {
    Assert::notNull($this->remoteContactId);

    /** @phpstan-var applicationProcessT|null $applicationProcessValues */
    $applicationProcessValues = $this->_remoteFundingEntityManager->getById(
      'FundingApplicationProcess',
      $applicationProcessId,
      $this->remoteContactId,
    );
    Assert::notNull($applicationProcessValues,
      E::ts('Application process with ID "%1" not found', [1 => $applicationProcessId]));
    $applicationProcess = ApplicationProcessEntity::fromArray($applicationProcessValues);

    /** @phpstan-var fundingCaseT|null $fundingCaseValues */
    $fundingCaseValues = $this->_remoteFundingEntityManager->getById(
      FundingCase::_getEntityName(),
      $applicationProcess->getFundingCaseId(),
      $this->remoteContactId,
    );
    Assert::notNull($fundingCaseValues,
      E::ts('Funding case with ID "%1" not found', [1 => $applicationProcess->getFundingCaseId()]));
    $fundingCase = FundingCaseEntity::fromArray($fundingCaseValues);

    /** @phpstan-var fundingCaseTypeT|null $fundingCaseTypeValues */
    $fundingCaseTypeValues = $this->_remoteFundingEntityManager->getById(
      FundingCaseType::_getEntityName(),
      $fundingCase->getFundingCaseTypeId(),
      $this->remoteContactId,
    );
    Assert::notNull(
      $fundingCaseTypeValues,
      E::ts('Funding case type with ID "%1" not found', [1 => $fundingCase->getId()])
    );
    $fundingCaseType = FundingCaseTypeEntity::fromArray($fundingCaseTypeValues);

    /** @var fundingProgramT|null $fundingProgramValues */
    $fundingProgramValues = $this->_remoteFundingEntityManager->getById(
      FundingProgram::_getEntityName(),
      $fundingCase->getFundingProgramId(),
      $this->remoteContactId,
    );
    Assert::notNull(
      $fundingProgramValues,
      E::ts('Funding program with ID "%1" not found', [1 => $fundingCase->getFundingProgramId()])
    );
    $fundingProgram = FundingProgramEntity::fromArray($fundingProgramValues);

    return $this->getExtraParams() + [
      'applicationProcess' => $applicationProcess,
      'fundingCase' => $fundingCase,
      'fundingCaseType' => $fundingCaseType,
      'fundingProgram' => $fundingProgram,
    ];
  }

}
