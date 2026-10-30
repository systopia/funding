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
use Civi\Funding\Entity\PayoutProcessBundle;
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
    if (NULL === $action->getAmountExact() && NULL === $action->getAmountPercent()) {
      throw new \InvalidArgumentException('Either amountExact or amountPercent must be set');
    }

    if (NULL !== $action->getAmountExact() && NULL !== $action->getAmountPercent()) {
      throw new \InvalidArgumentException('Either amountExact or amountPercent must be set, but not both');
    }

    $drawdowns = [];

    if (NULL !== $action->getAmountPercent()) {
      Assert::positiveInteger($action->getAmountPercent());
      Assert::lessThanEq($action->getAmountPercent(), 100);

      foreach ($action->getIds() as $id) {
        $drawdown = $this->createDrawdownPercent($id, $action->getAmountPercent());
        if (NULL !== $drawdown) {
          $drawdowns[$id] = $drawdown->toArray();
        }
      }
    }
    else {
      Assert::greaterThan($action->getAmountExact(), 0);

      $amountExact = round($action->getAmountExact(), 2);
      foreach ($action->getIds() as $id) {
        $drawdown = $this->createDrawdownExact($id, $amountExact);
        if (NULL !== $drawdown) {
          $drawdowns[$id] = $drawdown->toArray();
        }
      }
    }

    return $drawdowns;
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  private function createDrawdownExact(int $fundingCaseId, float $amountExact): ?DrawdownEntity {
    $payoutProcessBundle = $this->payoutProcessManager->getLastBundleByFundingCaseId($fundingCaseId);
    Assert::notNull(
      $payoutProcessBundle,
      "Payout process for funding case ID $fundingCaseId not found"
    );

    return $this->doCreateDrawdown($payoutProcessBundle, $amountExact);
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  private function createDrawdownPercent(int $fundingCaseId, int $amountPercent): ?DrawdownEntity {
    $payoutProcessBundle = $this->payoutProcessManager->getLastBundleByFundingCaseId($fundingCaseId);
    Assert::notNull(
      $payoutProcessBundle,
      "Payout process for funding case ID $fundingCaseId not found"
    );

    $amount = round($payoutProcessBundle->getPayoutProcess()->getAmountTotal() * $amountPercent / 100, 2);

    return $this->doCreateDrawdown($payoutProcessBundle, $amount);
  }

  private function doCreateDrawdown(PayoutProcessBundle $payoutProcessBundle, float $amount): ?DrawdownEntity {
    $fundingCase = $payoutProcessBundle->getFundingCase();
    if (!$fundingCase->hasPermission(FundingCasePermissions::REVIEW_DRAWDOWN_CREATE)) {
      throw new UnauthorizedException("Drawdown creation for funding case ID {$fundingCase->getId()} is not allowed.");
    }

    $amountRemaining = $this->payoutProcessManager->getAmountAvailable($payoutProcessBundle->getPayoutProcess());
    $amount = min($amount, $amountRemaining);
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
