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

namespace Civi\Funding\Api4\Action\FundingTransferContract;

use Civi\Api4\Contact;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\FundingTransferContract;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractGetAction {

  use ArrayQueryActionTrait;

  use IsFieldSelectedTrait;

  private Api4Interface $api4;

  private FundingCaseManager $fundingCaseManager;

  private FundingProgramManager $fundingProgramManager;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    FundingProgramManager $fundingProgramManager,
    PayoutProcessManager $payoutProcessManager
  ) {
    parent::__construct(FundingTransferContract::getEntityName(), 'get');
    $this->api4 = $api4;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $applicationProcessMapping = [];
    if ($this->isFieldExplicitlySelected('application_process_identifiers')) {
      $applicationProcessMapping['identifier'] = 'application_process_identifiers';
    }
    if ($this->isFieldExplicitlySelected('application_process_titles')) {
      $applicationProcessMapping['title'] = 'application_process_titles';
    }

    $records = [];
    foreach ($this->getFundingCases() as $fundingCase) {
      $records[] = $this->buildRecord($fundingCase, $applicationProcessMapping);
    }

    if ([] !== $records && in_array('*', $this->getSelect(), TRUE)) {
      $this->setSelect(array_keys($records[0]));
    }

    $this->queryArray($records, $result);
  }

  /**
   * @phpstan-param array<string, string> $applicationProcessMapping
   *
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  private function buildRecord(FundingCaseEntity $fundingCase, array $applicationProcessMapping): array {
    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf(
      'No permission to access funding program with ID "%d"',
      $fundingCase->getFundingProgramId()
    ));
    $payoutProcess = $this->payoutProcessManager->getLastByFundingCaseId($fundingCase->getId());
    Assert::notNull($payoutProcess, sprintf(
      'Payout process with funding case ID "%d" not found',
      $fundingCase->getId()
    ));
    $amountAvailable = $this->payoutProcessManager->getAmountAvailable($payoutProcess);

    $clearingProcessFields = array_intersect([
      'amount_recorded_costs',
      'amount_recorded_resources',
      'amount_admitted_costs',
      'amount_admitted_resources',
      'amount_cleared',
      'amount_admitted',
    ], $this->getSelect());
    if ([] !== $clearingProcessFields) {
      $clearingProcessAmounts = $this->api4->execute(FundingClearingProcess::getEntityName(), 'get', [
        'select' => array_map(fn (string $field) => 'SUM(' . $field . ') AS SUM_' . $field, $clearingProcessFields),
        'where' => [
          ['application_process_id.funding_case_id', '=', $fundingCase->getId()],
        ],
        'groupBy' => ['application_process_id.funding_case_id'],
      ])->first();
    }

    $record = [
      'funding_case_id' => $fundingCase->getId(),
      'identifier' => $fundingCase->getIdentifier(),
      'amount_approved' => $fundingCase->getAmountApproved(),
      'payout_process_id' => $payoutProcess->getId(),
      'amount_paid_out' => $this->payoutProcessManager->getAmountAccepted($payoutProcess),
      'amount_available' => $amountAvailable,
      'transfer_contract_uri' => $fundingCase->getTransferContractUri(),
      'funding_case_type_id' => $fundingCase->getFundingCaseTypeId(),
      'funding_program_id' => $fundingProgram->getId(),
      'currency' => $fundingProgram->getCurrency(),
      'funding_program_title' => $fundingProgram->getTitle(),
      'CAN_create_drawdown'
      => in_array('drawdown_create', $fundingCase->getPermissions(), TRUE) && 'closed' !== $payoutProcess->getStatus(),
    ];

    if ($this->isFieldExplicitlySelected('creation_contact_display_name')) {
      $record['creation_contact_display_name'] = $this->api4->getEntity(
        Contact::getEntityName(),
        $fundingCase->getCreationContactId()
      )['display_name'] ?? '';
    }

    if ($this->isFieldExplicitlySelected('recipient_contact_display_name')) {
      $record['recipient_contact_display_name'] = $this->api4->getEntity(
        Contact::getEntityName(),
        $fundingCase->getRecipientContactId()
      )['display_name'] ?? '';
    }

    if ([] !== $applicationProcessMapping) {
      $applicationProcesses = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
        'select' => array_keys($applicationProcessMapping),
        'where' => [
          ['funding_case_id', '=', $fundingCase->getId()],
          ['is_eligible', '=', TRUE],
        ],
        'orderBy' => ['id' => 'ASC'],
      ]);

      foreach ($applicationProcessMapping as $applicationProcessField => $resultField) {
        $record[$resultField] = implode(', ', $applicationProcesses->column($applicationProcessField));
      }
    }

    foreach ($clearingProcessFields as $field) {
      $record[$field] = $clearingProcessAmounts['SUM_' . $field] ?? NULL;
    }

    return $record;
  }

  private function getFundingCaseIdFromWhere(): ?int {
    return WhereUtil::getInt($this->where, 'funding_case_id');
  }

  /**
   * @return array<\Civi\Funding\Entity\FundingCaseEntity>
   *
   * @throws \CRM_Core_Exception
   */
  private function getFundingCases(): array {
    $fundingCaseId = $this->getFundingCaseIdFromWhere();
    if (NULL === $fundingCaseId) {
      return $this->fundingCaseManager->getBy(
        Comparison::new('amount_approved', '>', 0)
      );
    }

    $fundingCase = $this->fundingCaseManager->get($fundingCaseId);
    if (NULL !== $fundingCase && $fundingCase->getAmountApproved() > 0) {
      return [$fundingCase];
    }

    return [];
  }

}
