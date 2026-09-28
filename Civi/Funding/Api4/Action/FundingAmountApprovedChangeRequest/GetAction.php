<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest;

use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Api4\FundingCase;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class GetAction extends AbstractReferencingDAOGetAction {

  private bool $canReviewSelected;

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(
      FundingAmountApprovedChangeRequest::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
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
      if (!$this->isFieldSelected($this->_fundingCaseIdFieldName)) {
        $this->addSelect($this->_fundingCaseIdFieldName);
      }
    }

    parent::_run($result);
  }

  protected function handleRecord(array &$record): bool {
    if (!parent::handleRecord($record)) {
      return FALSE;
    }

    if ($this->canReviewSelected) {
      $record['CAN_review'] = $this->canReview(
        // @phpstan-ignore argument.type
        $record['status'],
        // @phpstan-ignore argument.type
        $record[$this->_fundingCaseIdFieldName]
      );
      $this->unsetIfNotSelected($record, 'status');
    }

    return TRUE;
  }

  /**
   * @return bool
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function canReview(string $status, int $fundingCaseId): bool {
    if ($status !== 'new') {
      return FALSE;
    }

    $possibleActions = $this->getApi4()->executeAction(
      FundingCase::getPossibleActions()->setId($fundingCaseId)
    );

    return in_array('review-amount-approved-change-request', (array) $possibleActions, TRUE);
  }

}
