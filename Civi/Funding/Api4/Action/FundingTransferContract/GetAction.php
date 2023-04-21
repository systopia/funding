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

use Civi\Api4\FundingTransferContract;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\Api4\Query\Comparison;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractGetAction {

  use ArrayQueryActionTrait;

  private FundingCaseManager $fundingCaseManager;

  private FundingProgramManager $fundingProgramManager;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingProgramManager $fundingProgramManager,
    PayoutProcessManager $payoutProcessManager
  ) {
    parent::__construct(FundingTransferContract::_getEntityName(), 'get');
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
    $records = [];
    foreach ($this->getFundingCases() as $fundingCase) {
      $records[] = $this->buildRecord($fundingCase);
    }

    $this->queryArray($records, $result);
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  private function buildRecord(FundingCaseEntity $fundingCase): array {
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

    return [
      'funding_case_id' => $fundingCase->getId(),
      'title' => $fundingCase->getTitle(),
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
