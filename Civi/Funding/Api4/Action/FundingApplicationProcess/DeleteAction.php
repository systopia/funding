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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\AbstractBatchAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\ApplicationProcess\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type applicationProcessT array{
 *   id: int,
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
 */
final class DeleteAction extends AbstractBatchAction {

  private Api4Interface $api4;

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    Api4Interface $api4,
    ApplicationProcessManager $applicationProcessManager,
    ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    FundingCaseManager $fundingCaseManager
  ) {
    parent::__construct(FundingApplicationProcess::_getEntityName(), 'delete');
    $this->api4 = $api4;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->actionsDeterminer = $actionsDeterminer;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  public function _run(Result $result): void {
    $applicationProcesses = $this->getApplicationProcesses();
    $fundingCases = [];
    foreach ($applicationProcesses as $applicationProcess) {
      /** @var \Civi\Funding\Entity\FundingCaseEntity $fundingCase */
      $fundingCase = $this->fundingCaseManager->get($applicationProcess->getFundingCaseId());
      $fundingCases[$applicationProcess->getId()] = $fundingCase;
      if (!$this->isDeleteAllowed($applicationProcess, $fundingCase)) {
        throw new UnauthorizedException('Deletion is not allowed');
      }
    }

    foreach ($applicationProcesses as $applicationProcess) {
      $this->applicationProcessManager->delete($applicationProcess, $fundingCases[$applicationProcess->getId()]);
      $result[] = ['id' => $applicationProcess->getId()];
    }
  }

  /**
   * @phpstan-return array<ApplicationProcessEntity>
   * @throws \API_Exception
   */
  private function getApplicationProcesses(): array {
    $action = FundingApplicationProcess::get()->setWhere($this->getWhere());

    /** @var array<applicationProcessT> $records */
    $records = $this->api4->executeAction($action)->getArrayCopy();

    return \array_map(
      fn (array $values) => ApplicationProcessEntity::fromArray($values),
      $records,
    );
  }

  private function isDeleteAllowed(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): bool {
    return $this->actionsDeterminer->isActionAllowed(
      'delete',
      $applicationProcess->getStatus(),
      $fundingCase->getPermissions(),
    );
  }

}
