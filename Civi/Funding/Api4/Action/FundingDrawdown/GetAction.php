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
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class GetAction extends AbstractReferencingDAOGetAction {

  private bool $canReviewSelected;

  /**
   * @phpstan-var array<FundingCaseEntity>
   */
  private array $fundingCases = [];

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(
      FundingDrawdown::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
    $this->_fundingCaseIdFieldName = 'payout_process_id.funding_case_id';
  }

  public function _run(Result $result): void {
    $this->initOriginalSelect();
    $this->canReviewSelected = $this->isFieldExplicitlySelected('CAN_review');

    if ([] === $this->getSelect()) {
      $this->setSelect(['*']);
    }

    if ($this->canReviewSelected) {
      if (!$this->isFieldSelected('status')) {
        $this->addSelect('status');
      }
      if (!$this->isFieldSelected('payout_process_id.status')) {
        $this->addSelect('payout_process_id.status');
      }
    }

    parent::_run($result);
  }

  protected function ensureFundingCasePermissions(): void {
    // Ensure permissions for all funding cases with payout process are determined.
    $action = FundingCase::get(FALSE)
      ->setCachePermissionsOnly(TRUE)
      ->addJoin(FundingPayoutProcess::getEntityName() . ' AS pp', 'INNER', NULL, ['pp.funding_case_id', '=', 'id']);

    $payoutProcessId = WhereUtil::getInt($this->getWhere(), 'payout_process_id');
    if (NULL !== $payoutProcessId) {
      $action->addWhere('pp.id', '=', $payoutProcessId);
    }

    $this->getApi4()->executeAction($action);
  }

  protected function handleRecord(array &$record): bool {
    if (!parent::handleRecord($record)) {
      return FALSE;
    }

    if ($this->canReviewSelected) {
      $record['CAN_review'] = $this->getCanReview(
        $record['status'],
        $record['payout_process_id.status'],
        $record[$this->_fundingCaseIdFieldName]
      );
      $this->unsetIfNotSelected($record, 'status');
      $this->unsetIfNotSelected($record, 'payout_process_id.status');
    }

    return TRUE;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFundingCase(int $id): FundingCaseEntity {
    if (!isset($this->fundingCases[$id])) {
      $fundingCase = $this->getFundingCaseManager()->get($id);
      Assert::notNull($fundingCase, sprintf('Funding case with ID "%d" not found', $id));
      $this->fundingCases[$id] = $fundingCase;
    }

    return $this->fundingCases[$id];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getCanReview(string $drawdownStatus, string $payoutProcessStatus, int $fundingCaseId): bool {
    $fundingCase = $this->getFundingCase($fundingCaseId);

    return 'new' === $drawdownStatus
      // Final reviewers are allowed to accept/reject final drawdown in case it was created as "new".
      && ('open' === $payoutProcessStatus || $fundingCase->hasPermission(FundingCasePermissions::REVIEW_FINISH))
      && $fundingCase->hasPermission('review_drawdown');
  }

}
