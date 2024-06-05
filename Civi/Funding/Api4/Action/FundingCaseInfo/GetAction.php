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
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\Api4\Query\ConditionInterface;

final class GetAction extends AbstractGetAction {

  use ArrayQueryActionTrait {
    ArrayQueryActionTrait::filterCompare as traitFilterCompare;
  }

  use IsFieldSelectedTrait;

  private Api4Interface $api4;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  /**
   * @phpstan-param array<string, mixed> $row
   * @phpstan-param array<mixed> $condition
   *
   * @return bool
   *
   * @throws \Civi\API\Exception\NotImplementedException
   *
   * @see ArrayQueryActionTrait::filterCompare()
   */
  public static function filterCompare($row, $condition) {
    if (is_string($row[$condition[0] ?? NULL] ?? NULL) && 'LIKE' === ($condition[1] ?? NULL)) {
      $expected = $condition[2] ?? NULL;
      if (is_string($expected) && str_starts_with($expected, '%') && str_ends_with($expected, '%')) {
        // Make case-insensitive comparison if we're looking for "%<something>%".
        // This makes searching (in Drupal Views) behaving as accustomed to users.
        $condition[1] = 'CONTAINS';
        $condition[2] = trim($expected, '%');
      }
    }

    return static::traitFilterCompare($row, $condition);
  }

  public function __construct(
    Api4Interface $api4,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader
  ) {
    parent::__construct(FundingCaseInfo::getEntityName(), 'get');
    $this->api4 = $api4;
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    if ($this->isFieldExplicitlySelected('CAN_open_clearing')) {
      $this->addSelect('clearing_process_id');
    }

    $records = [];
    foreach ($this->getApplicationProcessBundles() as $applicationProcessBundle) {
      $records[] = $this->buildRecord($applicationProcessBundle);
    }

    if (in_array('*', $this->getSelect(), TRUE)) {
      // Don't filter not explicitly selected fields in queryArray().
      $this->setSelect([]);
    }

    $this->queryArray($records, $result);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function buildRecord(ApplicationProcessEntityBundle $applicationProcessBundle): array {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingCaseType = $applicationProcessBundle->getFundingCaseType();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

    $clearingProcessFields = array_intersect([
      'clearing_process_id',
      'clearing_process_amount_recorded_costs',
      'clearing_process_amount_recorded_resources',
      'clearing_process_amount_admitted_costs',
      'clearing_process_amount_admitted_resources',
      'clearing_process_amount_cleared',
      'clearing_process_amount_admitted',
    ], $this->getSelect());
    if ([] !== $clearingProcessFields) {
      $clearingProcessAmounts = $this->api4->execute(FundingClearingProcess::getEntityName(), 'get', [
        'select' => array_map(fn (string $field) => substr($field, 17), $clearingProcessFields),
        'where' => [
          CompositeCondition::fromFieldValuePairs([
            'application_process_id' => $applicationProcess->getId(),
            'status' => 'accepted',
          ])->toArray(),
        ],
      ])->first();
    }

    $record = [
      'funding_case_id' => $fundingCase->getId(),
      'funding_case_identifier' => $fundingCase->getIdentifier(),
      'funding_case_permissions' => $fundingCase->getPermissions(),
      'funding_case_status' => $fundingCase->getStatus(),
      'funding_case_creation_date' => $fundingCase->getCreationDate()->format('Y-m-d H:i:s'),
      'funding_case_modification_date' => $fundingCase->getModificationDate()->format('Y-m-d H:i:s'),
      'funding_case_amount_approved' => $fundingCase->getAmountApproved(),
      'funding_case_type_id' => $fundingCaseType->getId(),
      'funding_case_type_is_combined_application' => $fundingCaseType->getIsCombinedApplication(),
      'funding_case_transfer_contract_uri' => $fundingCase->getTransferContractUri(),
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
      'application_process_creation_date' => $applicationProcess->getCreationDate()->format('Y-m-d H:i:s'),
      'application_process_modification_date' => $applicationProcess->getModificationDate()->format('Y-m-d H:i:s'),
      'application_process_start_date' => self::toFormattedDateOrNull($applicationProcess->getStartDate()),
      'application_process_end_date' => self::toFormattedDateOrNull($applicationProcess->getEndDate()),
      'application_process_is_eligible' => $applicationProcess->getIsEligible(),
    ];

    foreach ($clearingProcessFields as $field) {
      $record[$field] = $clearingProcessAmounts[substr($field, 17)] ?? NULL;
    }

    foreach ($fundingCase->getFlattenedPermissions() as $permission => $active) {
      $record['funding_case_' . $permission] = $active;
    }

    if ($this->isFieldExplicitlySelected('CAN_open_clearing')) {
      $record['CAN_open_clearing'] = $this->canOpenClearing(
        $record['application_process_is_eligible'],
        $record['clearing_process_id'],
        $record['funding_case_permissions']
      );
    }

    return $record;
  }

  private static function toFormattedDateOrNull(?\DateTimeInterface $date): ?string {
    return NULL === $date ? NULL : $date->format('Y-m-d H:i:s');
  }

  /**
   * @phpstan-return iterable<ApplicationProcessEntityBundle>
   *
   * @throws \CRM_Core_Exception
   */
  private function getApplicationProcessBundles(): iterable {
    $conditions = $this->buildCondition();
    if (isset($conditions)) {
      return $this->applicationProcessBundleLoader->getBy($conditions);
    }
    return $this->applicationProcessBundleLoader->getAll();
  }

  /**
   * @return \Civi\RemoteTools\Api4\Query\ConditionInterface|null
   */
  private function buildCondition(): ?ConditionInterface {
    $fieldNameMapping = [
      'funding_case_type_is_combined_application' => 'funding_case_id.funding_case_type_id.is_combined_application',
      'funding_program_title' => 'funding_case_id.funding_program_id.title',
      'application_process_id' => 'id',
      'application_process_status' => 'status',
      'funding_case_id' => 'funding_case_id',
    ];
    $conditions = [];
    foreach ($this->where as $condition) {
      if (array_key_exists($condition[0], $fieldNameMapping)) {
        $conditions[] = Comparison::new($fieldNameMapping[$condition[0]], $condition[1], $condition[2]);
      }
    }
    if ($conditions !== []) {
      return CompositeCondition::new('AND', ...$conditions);
    }

    return NULL;
  }

  /**
   * @phpstan-param list<string> $permissions
   */
  private function canOpenClearing(?bool $isEligible, ?int $clearingProcessId, array $permissions): bool {
    return TRUE === $isEligible && (NULL !== $clearingProcessId
      || in_array(ClearingProcessPermissions::CLEARING_MODIFY, $permissions, TRUE)
      || in_array(ClearingProcessPermissions::CLEARING_APPLY, $permissions, TRUE));
  }

}
