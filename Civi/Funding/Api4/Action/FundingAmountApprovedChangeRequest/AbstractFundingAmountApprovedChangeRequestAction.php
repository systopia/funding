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

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Api4\FundingCase;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\IdsParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @method self setIds(list<int> $ids)
 */
abstract class AbstractFundingAmountApprovedChangeRequestAction extends AbstractAction {

  use IdsParameterTrait;

  public function _run(Result $result): void {
    $processed = [];

    foreach ($this->ids as $id) {
      /** @var array<string, mixed> $request */
      $request = FundingAmountApprovedChangeRequest::get(FALSE)
        ->addWhere('id', '=', $id)
        ->execute()->single();

      if ($request['status'] !== 'new') {
        continue;
      }

      Assert::integerish($request['funding_case_id']);
      $fundingCaseId = (int) $request['funding_case_id'];

      $fundingCase = FundingCase::get(FALSE)
        ->addSelect('id')
        ->addWhere('id', '=', $fundingCaseId)
        ->execute()
        ->first();

      Assert::notNull($fundingCase, E::ts('Funding case with ID "%1" not found', [1 => $fundingCaseId]));

      $possibleActions = FundingCase::getPossibleActions()
        ->setId($fundingCaseId)
        ->execute();

      $canReview = FALSE;
      foreach ($possibleActions as $action) {
        if ($action === 'review-amount-approved-change-request') {
          $canReview = TRUE;
          break;
        }
      }

      if (!$canReview) {
        throw new UnauthorizedException(E::ts('Not authorized to review this change request.'));
      }

      $res = $this->processRequest($id, $request);
      if ($res !== NULL) {
        $processed[$id] = $res;
      }
    }
    $result->exchangeArray($processed);
  }

  /**
   * @param int $id
   * @param array<string, mixed> $request
   *
   * @return array<string, mixed>|null
   */
  abstract protected function processRequest(int $id, array $request): ?array;

}
