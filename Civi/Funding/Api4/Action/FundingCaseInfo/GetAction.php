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

namespace Civi\Funding\Api4\Action\FundingCaseInfo;

use Civi\Api4\FundingCaseInfo;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractGetAction {

  use ArrayQueryActionTrait;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    FundingProgramManager $fundingProgramManager
  ) {
    parent::__construct(FundingCaseInfo::_getEntityName(), 'get');
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $records = [];
    foreach ($this->getFundingCases() as $fundingCase) {
      $applicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
      if (NULL === $applicationProcess) {
        continue;
      }

      $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
      Assert::notNull($fundingProgram);

      $records[] = $this->buildRecord($fundingCase, $fundingProgram, $applicationProcess);
    }

    if ($this->isRowCountSelected()) {
      $result->setCountMatched(count($records));
    }

    if (!$this->isRowCountSelectedOnly()) {
      $records = $this->sortArray($records);
      $records = $this->limitArray($records);
      $records = $this->selectArray($records);
      $result->exchangeArray($records);
    }
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function buildRecord(
    FundingCaseEntity $fundingCase,
    FundingProgramEntity $fundingProgram,
    ApplicationProcessEntity $applicationProcess
  ): array {
    $record = [
      'funding_case_id' => $fundingCase->getId(),
      'funding_case_permissions' => $fundingCase->getPermissions(),
      'funding_case_status' => $fundingCase->getStatus(),
      'funding_case_creation_date' => $fundingCase->getCreationDate()->format('Y-m-d H:i:s'),
      'funding_case_modification_date' => $fundingCase->getModificationDate()->format('Y-m-d H:i:s'),
      'funding_case_type_id' => $fundingCase->getFundingCaseTypeId(),
      'funding_program_id' => $fundingProgram->getId(),
      'funding_program_currency' => $fundingProgram->getCurrency(),
      'funding_program_title' => $fundingProgram->getTitle(),
      'application_process_id' => $applicationProcess->getId(),
      'application_process_identifier' => $applicationProcess->getIdentifier(),
      'application_process_title' => $applicationProcess->getTitle(),
      'application_process_short_description' => $applicationProcess->getShortDescription(),
      'application_process_status' => $applicationProcess->getStatus(),
      'application_process_is_review_calculative' => $applicationProcess->getIsReviewContent(),
      'application_process_is_review_content' => $applicationProcess->getIsReviewContent(),
      'application_process_amount_requested' => $applicationProcess->getAmountRequested(),
      'application_process_amount_granted' => $applicationProcess->getAmountGranted(),
      'application_process_granted_budget' => $applicationProcess->getGrantedBudget(),
      'application_process_creation_date' => $applicationProcess->getCreationDate()->format('Y-m-d H:i:s'),
      'application_process_modification_date' => $applicationProcess->getModificationDate()->format('Y-m-d H:i:s'),
      'application_process_start_date' => self::toFormattedDateOrNull($applicationProcess->getStartDate()),
      'application_process_end_date' => self::toFormattedDateOrNull($applicationProcess->getEndDate()),
    ];

    foreach ($fundingCase->getFlattenedPermissions() as $permission => $active) {
      $record['funding_case_' . $permission] = $active;
    }

    return $record;
  }

  private static function toFormattedDateOrNull(?\DateTimeInterface $date): ?string {
    return NULL === $date ? NULL : $date->format('Y-m-d H:i:s');
  }

  private function getFundingCaseIdFromWhere(): ?int {
    foreach ($this->where as $clause) {
      if ($clause[0] === 'funding_case_id' && '=' === $clause[1] && is_int($clause[2])) {
        return $clause[2];
      }
    }

    return NULL;
  }

  /**
   * @return array<FundingCaseEntity>
   *
   * @throws \API_Exception
   */
  private function getFundingCases(): array {
    $fundingCaseId = $this->getFundingCaseIdFromWhere();
    if (NULL === $fundingCaseId) {
      return $this->fundingCaseManager->getAll();
    }

    $fundingCase = $this->fundingCaseManager->get($fundingCaseId);

    return NULL === $fundingCase ? [] : [$fundingCase];
  }

  private function isRowCountSelected(): bool {
    return in_array('row_count', $this->getSelect(), TRUE);
  }

  private function isRowCountSelectedOnly(): bool {
    return ['row_count'] === $this->getSelect();
  }

}
