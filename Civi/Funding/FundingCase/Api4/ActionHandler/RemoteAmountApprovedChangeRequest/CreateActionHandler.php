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

namespace Civi\Funding\FundingCase\Api4\ActionHandler\RemoteAmountApprovedChangeRequest;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Api4\FundingCase;
use Civi\Funding\Api4\Action\Remote\AmountApprovedChangeRequest\CreateAction;
use Civi\Funding\FundingCase\Actions\FundingCaseActions as Actions;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class CreateActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingAmountApprovedChangeRequest';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @param \Civi\Funding\Api4\Action\Remote\AmountApprovedChangeRequest\CreateAction $action
   *
   * @return array<string, mixed>
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function create(CreateAction $action): array {
    $fundingCaseId = $action->getFundingCaseId();

    $possibleActions = $this->api4->executeAction(
      FundingCase::getPossibleActions(FALSE)->setId($fundingCaseId)
    );

    $canCreate = FALSE;
    foreach ($possibleActions as $actionName) {
      if ($actionName === Actions::CREATE_AMOUNT_REVIEW_CHANGE_REQUEST) {
        $canCreate = TRUE;
        break;
      }
    }

    if (!$canCreate) {
      throw new UnauthorizedException('Not authorized to create this change request.');
    }

    $createAction = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCaseId)
      ->addValue('amount_requested', $action->getAmountRequested())
      ->addValue('comment', $action->getComment())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $action->getResolvedContactId());
    $result = $this->api4->executeAction($createAction);

    return [$result->single()];
  }

}
