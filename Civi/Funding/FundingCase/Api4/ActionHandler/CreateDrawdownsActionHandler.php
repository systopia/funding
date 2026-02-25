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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\FundingCase\CreateDrawdownsAction;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type drawdownT from DrawdownEntity
 */
final class CreateDrawdownsActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCase';

  private DrawdownManager $drawdownManager;

  private PayoutProcessManager $payoutProcessManager;

  private RequestContextInterface $requestContext;

  public function __construct(
    DrawdownManager $drawdownManager,
    PayoutProcessManager $payoutProcessManager,
    RequestContextInterface $requestContext
  ) {
    $this->drawdownManager = $drawdownManager;
    $this->payoutProcessManager = $payoutProcessManager;
    $this->requestContext = $requestContext;
  }

  /**
   * @phpstan-return array<int, drawdownT>
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function createDrawdowns(CreateDrawdownsAction $action): array {
    Assert::positiveInteger($action->getAmountPercent());
    Assert::lessThanEq($action->getAmountPercent(), 100);

    $drawdowns = [];

    foreach ($action->getIds() as $id) {
      $drawdown = $this->createDrawdown($id, $action->getAmountPercent());
      if (NULL !== $drawdown) {
        $drawdowns[$id] = $drawdown->toArray();
      }
    }

    return $drawdowns;
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  private function createDrawdown(int $fundingCaseId, int $amountPercent): ?DrawdownEntity {
    $payoutProcessBundle = $this->payoutProcessManager->getLastBundleByFundingCaseId($fundingCaseId);
    Assert::notNull(
      $payoutProcessBundle,
      "Payout process for funding case ID $fundingCaseId not found"
    );

    $fundingCase = $payoutProcessBundle->getFundingCase();
    if (!$fundingCase->hasPermission(FundingCasePermissions::REVIEW_DRAWDOWN_CREATE)) {
      throw new UnauthorizedException("Drawdown creation for funding case ID $fundingCaseId is not allowed.");
    }

    $payoutProcess = $payoutProcessBundle->getPayoutProcess();
    $amountRemaining = $this->payoutProcessManager->getAmountAvailable($payoutProcess);
    $amount = min(round($payoutProcess->getAmountTotal() * $amountPercent / 100, 2), $amountRemaining);

    if (0.0 === $amount) {
      return NULL;
    }

    return $this->drawdownManager->createNew(
      $payoutProcessBundle,
      $amount,
      $this->requestContext->getContactId(),
    );
  }

}
