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

namespace Civi\Funding\FundingAmountApprovedChangeRequest\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Api4\FundingCase;
use Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\ApprovePartialAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingAmountApprovedChangeRequestEntity;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class ApprovePartialActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingAmountApprovedChangeRequest';

  private Api4Interface $api4;

  private FundingCaseManager $fundingCaseManager;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseActionsDeterminerInterface $fundingCaseActionsDeterminer;

  private RequestContextInterface $requestContext;

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseActionsDeterminerInterface $fundingCaseActionsDeterminer,
    RequestContextInterface $requestContext,
  ) {
    $this->api4 = $api4;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseActionsDeterminer = $fundingCaseActionsDeterminer;
    $this->requestContext = $requestContext;
  }

  /**
   * @return array<int, array<string, mixed>>
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function approvePartial(ApprovePartialAction $action): array {
    $processed = [];
    $amount = $action->getAmountApproved();

    Assert::greaterThan($amount, 0);

    foreach ($action->getIds() as $id) {
      $requestEntity = FundingAmountApprovedChangeRequestEntity::singleFromApiResult(
        $this->api4->executeAction(
          FundingAmountApprovedChangeRequest::get(FALSE)
            ->addWhere('id', '=', $id)
        )
      );

      if ($requestEntity->getStatus() !== 'new') {
        continue;
      }

      $fundingCaseId = $requestEntity->getFundingCaseId();

      $fundingCaseBundle = $this->fundingCaseManager->getBundle($fundingCaseId);
      Assert::notNull($fundingCaseBundle, E::ts('Funding case with ID "%1" not found', [1 => $fundingCaseId]));

      $canReview = $this->fundingCaseActionsDeterminer->isActionAllowed(
        FundingCaseActions::REVIEW_AMOUNT_APPROVED_CHANGE_REQUEST,
        $fundingCaseBundle,
        $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCaseId),
      );

      if (!$canReview) {
        throw new UnauthorizedException('Not authorized to review this change request.');
      }

      $this->api4->executeAction(
        FundingAmountApprovedChangeRequest::update(FALSE)
          ->addWhere('id', '=', $id)
          ->setValues([
            'status' => 'approved_partial',
            'amount_approved' => $amount,
            'decision_date' => date('Y-m-d H:i:s'),
            'decision_contact_id' => $this->requestContext->getLoggedInContactId(),
          ])
      );

      $this->api4->executeAction(
        FundingCase::updateAmountApproved()
          ->setId($fundingCaseId)
          ->setAmount($amount)
      );

      $processed[$id] = [
        'id' => $id,
        'status' => 'approved_partial',
        'amount_approved' => $amount,
      ];
    }

    return $processed;
  }

}
