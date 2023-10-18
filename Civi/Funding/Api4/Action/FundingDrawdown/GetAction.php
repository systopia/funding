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

use Civi\Api4\FundingCase;
use Civi\Api4\FundingDrawdown;
use Civi\Api4\FundingPayoutProcess;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\Api4\Util\FundingCasePermissionsUtil;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class GetAction extends DAOGetAction {

  use IsFieldSelectedTrait;

  private Api4Interface $api4;

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

  private RequestContextInterface $requestContext;

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    FundingProgramManager $fundingProgramManager,
    PayoutProcessManager $payoutProcessManager,
    RequestContextInterface $requestContext
  ) {
    parent::__construct(FundingDrawdown::getEntityName(), 'get');
    $this->api4 = $api4;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->payoutProcessManager = $payoutProcessManager;
    $this->requestContext = $requestContext;
  }

  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
  public function _run(Result $result): void {
  // phpcs:enable
    $rowCountSelected = $this->isRowCountSelected();
    if ($rowCountSelected) {
      $this->ensureFundingCasePermissions();
    }

    FundingCasePermissionsUtil::addPermissionsCacheJoin(
      $this,
      'payout_process_id.funding_case_id',
      $this->requestContext->getContactId(),
      $this->requestContext->isRemote()
    );
    FundingCasePermissionsUtil::addPermissionsRestriction($this);

    $currencySelected = $this->isFieldSelected('currency');
    $canReviewSelected = $this->isFieldSelected('CAN_review');
    $payoutProcessIdSelected = $this->isFieldSelected('payout_process_id');

    if ($canReviewSelected && !$this->isFieldSelected('status')) {
      $this->addSelect('status');
    }

    if (!$payoutProcessIdSelected) {
      $this->addSelect('payout_process_id');
    }

    $limit = $this->getLimit();
    $offset = $this->getOffset();
    $records = [];
    do {
      parent::_run($result);

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

      $limitBefore = $this->getLimit();
      $this->setOffset($offset + count($records));
      $this->setLimit($limit - count($records));
    } while ($this->getLimit() > 0 && count($result) === $limitBefore);

    $result->exchangeArray($records);
    if (!$rowCountSelected) {
      $result->rowCount = count($records);
    }
  }

  private function ensureFundingCasePermissions(): void {
    // Ensure permissions for all funding cases with payout process are determined.
    $action = FundingCase::get(FALSE)
      ->addSelect('DISTINCT id')
      ->addJoin(FundingPayoutProcess::getEntityName() . ' AS pp', 'INNER', NULL, ['pp.funding_case_id', '=', 'id']);

    $payoutProcessId = WhereUtil::getInt($this->getWhere(), 'payout_process_id');
    if (NULL !== $payoutProcessId) {
      $action->addWhere('pp.id', '=', $payoutProcessId);
    }

    $this->api4->executeAction($action);
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

}
