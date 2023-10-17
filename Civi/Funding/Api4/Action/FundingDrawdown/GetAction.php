<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Util\SelectUtil;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Webmozart\Assert\Assert;

final class GetAction extends DAOGetAction {

  private FundingCaseManager $fundingCaseManager;

  /**
   * @phpstan-var array<FundingCaseEntity>
   */
  private array $fundingCases = [];

  private FundingProgramManager $fundingProgramManager;

  /**
   * @phpstan-var array<FundingProgramEntity>
   */
  private array $fundingPrograms = [];

  private PayoutProcessManager $payoutProcessManager;

  /**
   * @phpstan-var array<PayoutProcessEntity|null>
   */
  private array $payoutProcesses = [];

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingProgramManager $fundingProgramManager,
    PayoutProcessManager $payoutProcessManager
  ) {
    parent::__construct(FundingDrawdown::getEntityName(), 'get');
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  public function _run(Result $result): void {
    $payoutProcessId = WhereUtil::getInt($this->getWhere(), 'payout_process_id');
    if (NULL === $payoutProcessId) {
      $this->runDefault($result);
    }
    else {
      $this->runWithSinglePayoutProcessId($result, $payoutProcessId);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getCurrency(PayoutProcessEntity $payoutProcess): string {
    $fundingCase = $this->getFundingCase($payoutProcess->getFundingCaseId());
    $fundingProgram = $this->getFundingProgram($fundingCase->getFundingProgramId());

    return $fundingProgram->getCurrency();
  }

  private function getPayoutProcess(int $id): ?PayoutProcessEntity {
    if (!array_key_exists($id, $this->payoutProcesses)) {
      $this->payoutProcesses[$id] = $this->payoutProcessManager->get($id);
    }

    return $this->payoutProcesses[$id];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFundingCase(int $id): FundingCaseEntity {
    if (!isset($this->fundingCases[$id])) {
      $fundingCase = $this->fundingCaseManager->get($id);
      Assert::notNull($fundingCase, sprintf('Funding case with ID "%d" not found', $id));
      $this->fundingCases[$id] = $fundingCase;
    }

    return $this->fundingCases[$id];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFundingProgram(int $id): FundingProgramEntity {
    if (!isset($this->fundingPrograms[$id])) {
      $fundingProgram = $this->fundingProgramManager->get($id);
      Assert::notNull($fundingProgram, sprintf('Funding program with ID "%d" not found', $id));
      $this->fundingPrograms[$id] = $fundingProgram;
    }

    return $this->fundingPrograms[$id];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getCanReview(string $drawdownStatus, PayoutProcessEntity $payoutProcess): bool {
    return 'new' === $drawdownStatus
      && 'open' === $payoutProcess->getStatus()
      && $this->getFundingCase($payoutProcess->getFundingCaseId())->hasPermission('review_drawdown');
  }

  private function isFieldSelected(string $field): bool {
    return SelectUtil::isFieldSelected($field, $this->select);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function runDefault(Result $result): void {
    $currencySelected = $this->isFieldSelected('currency');
    $canReviewSelected = $this->isFieldSelected('CAN_review');
    $payoutProcessIdSelected = $this->isFieldSelected('payout_process_id');

    if ($canReviewSelected && !$this->isFieldSelected('status')) {
      $this->addSelect('status');
    }

    if (!$payoutProcessIdSelected) {
      $this->addSelect('payout_process_id');
    }
    parent::_run($result);

    $records = [];
    /** @phpstan-var array<string, mixed>&array{payout_process_id: int, status: string} $record */
    foreach ($result as $record) {
      $payoutProcess = $this->getPayoutProcess($record['payout_process_id']);
      if (NULL !== $payoutProcess) {
        if ($currencySelected) {
          $record['currency'] = $this->getCurrency($payoutProcess);
        }
        if ($canReviewSelected) {
          $record['CAN_review'] = $this->getCanReview($record['status'], $payoutProcess);
        }
        if (!$payoutProcessIdSelected) {
          unset($record['payout_process_id']);
        }

        $records[] = $record;
      }
    }

    $result->exchangeArray($records);
    $result->setCountMatched(count($records));
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function runWithSinglePayoutProcessId(Result $result, int $payoutProcessId): void {
    $payoutProcess = $this->getPayoutProcess($payoutProcessId);
    if (NULL !== $payoutProcess) {
      if ($this->isFieldSelected('CAN_review') && !$this->isFieldSelected('status')) {
        $this->addSelect('status');
      }
      parent::_run($result);
      if ($this->isFieldSelected('currency')) {
        /** @phpstan-var array<string, mixed> $record */
        foreach ($result as &$record) {
          $record['currency'] = $this->getCurrency($payoutProcess);
        }
      }
      if ($this->isFieldSelected('CAN_review')) {
        /** @phpstan-var array<string, mixed>&array{status: string} $record */
        foreach ($result as &$record) {
          $record['CAN_review'] = $this->getCanReview($record['status'], $payoutProcess);
        }
      }
    }
  }

}
