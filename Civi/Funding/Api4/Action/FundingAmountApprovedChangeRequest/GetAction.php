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

class GetAction extends AbstractReferencingDAOGetAction {

  public function __construct() {
    parent::__construct(FundingAmountApprovedChangeRequest::getEntityName(), NULL);
  }

  public function _run(Result $result): void {
    $this->initOriginalSelect();
    $canReviewSelected = $this->isFieldExplicitlySelected('CAN_review');

    parent::_run($result);

    if ($canReviewSelected) {
      /** @var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['CAN_review'] = $this->canReview($record);
      }
    }
  }

  /**
   * @param array<string, mixed> $record
   *
   * @return bool
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function canReview(array $record): bool {
    if (($record['status'] ?? NULL) !== 'new') {
      return FALSE;
    }

    $fundingCaseId = $record['funding_case_id'] ?? NULL;
    if (!is_numeric($fundingCaseId)) {
      return FALSE;
    }

    $possibleActions = FundingCase::getPossibleActions()
      ->setId((int) $fundingCaseId)
      ->execute();

    return in_array('review-amount-approved-change-request', (array) $possibleActions, TRUE);
  }

}
