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
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseManager;

final class GetAction extends AbstractGetAction {

  use ArrayQueryActionTrait;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    FundingCaseManager $fundingCaseManager
  ) {
    parent::__construct(FundingCaseInfo::_getEntityName(), 'get');
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $records = [];
    foreach ($this->getFundingCases() as $fundingCase) {
      $applicationProcessBundle = $this->applicationProcessBundleLoader->getFirstByFundingCaseId($fundingCase->getId());
      if (NULL === $applicationProcessBundle) {
        continue;
      }

      $records[] = $this->buildRecord($applicationProcessBundle);
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
  private function buildRecord(ApplicationProcessEntityBundle $applicationProcessBundle): array {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

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
   * @return array<\Civi\Funding\Entity\FundingCaseEntity>
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
