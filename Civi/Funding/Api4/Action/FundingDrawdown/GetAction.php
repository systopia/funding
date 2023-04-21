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
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Webmozart\Assert\Assert;

final class GetAction extends DAOGetAction {

  private FundingCaseManager $fundingCaseManager;

  private FundingProgramManager $fundingProgramManager;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingProgramManager $fundingProgramManager,
    PayoutProcessManager $payoutProcessManager
  ) {
    parent::__construct(FundingDrawdown::_getEntityName(), 'get');
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  public function _run(Result $result): void {
    $payoutProcessId = WhereUtil::getInt($this->getWhere(), 'payout_process_id');
    if (NULL !== $payoutProcessId) {
      $payoutProcess = $this->payoutProcessManager->get($payoutProcessId);
      if (NULL !== $payoutProcess) {
        parent::_run($result);
        if ($this->isCurrencySelected()) {
          /** @phpstan-var array<string, mixed> $record */
          foreach ($result as &$record) {
            $record['currency'] = $this->getCurrency($payoutProcess);
          }
        }
      }

      return;
    }

    $payoutProcessIdSelected = $this->isPayoutProcessIdSelected();
    if (!$payoutProcessIdSelected) {
      $this->addSelect('payout_process_id');
    }
    parent::_run($result);

    $currencySelected = $this->isCurrencySelected();
    $records = [];
    /** @phpstan-var array<string, mixed>&array{payout_process_id: int} $record */
    foreach ($result as $record) {
      $payoutProcess = $this->payoutProcessManager->get($record['payout_process_id']);
      if (NULL !== $payoutProcess) {
        if ($currencySelected) {
          $record['currency'] = $this->getCurrency($payoutProcess);
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

  private function getCurrency(PayoutProcessEntity $payoutProcess): string {
    $fundingCase = $this->fundingCaseManager->get($payoutProcess->getFundingCaseId());
    Assert::notNull($fundingCase, sprintf('Funding case with ID "%d" not found', $payoutProcess->getFundingCaseId()));
    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf(
      'Funding program with ID "%d" not found',
      $fundingCase->getFundingProgramId()
    ));

    return $fundingProgram->getCurrency();
  }

  private function isCurrencySelected(): bool {
    return SelectUtil::isFieldSelected('currency', $this->select);
  }

  private function isPayoutProcessIdSelected(): bool {
    return SelectUtil::isFieldSelected('payout_process_id', $this->select);
  }

}
