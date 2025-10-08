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

namespace Civi\Funding\PayoutProcess\Validator;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Civi\Funding\Validation\EntityValidationError;
use Civi\Funding\Validation\EntityValidationResult;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * Checks if user has permission to create/update a drawdown.
 *
 * @implements ConcreteEntityValidatorInterface<DrawdownEntity>
 */
final class DrawdownValidator implements ConcreteEntityValidatorInterface {

  private FundingCaseManager $fundingCaseManager;

  private PayoutProcessManager $payoutProcessManager;

  /**
   * @inheritDoc
   */
  public static function getEntityClass(): string {
    return DrawdownEntity::class;
  }

  public function __construct(FundingCaseManager $fundingCaseManager, PayoutProcessManager $payoutProcessManager) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @inheritDoc
   *
   * @param \Civi\Funding\Entity\DrawdownEntity $new
   * @param \Civi\Funding\Entity\DrawdownEntity $current
   *
   * phpcs:disable Drupal.Commenting.FunctionComment.IncorrectTypeHint
   */
  public function validate(
    AbstractEntity $new,
    AbstractEntity $current,
    bool $checkPermissions
  ): EntityValidationResult {
    $payoutProcess = $this->getPayoutProcess($new);

    // Final reviewers are allowed to accept/reject final drawdown in case it was created as "new".
    if ($current->getStatus() !== 'new' || $new->getStatus() === 'new'
      || !$this->getFundingCase($payoutProcess)->hasPermission(FundingCasePermissions::REVIEW_FINISH)
    ) {
      $this->assertNotClosed($payoutProcess);
    }

    if ($new->getAmount() < 0) {
      $fundingCase = $this->getFundingCase($payoutProcess);
      // Only reviewers are allowed to create payback claims.
      if (!$fundingCase->hasPermission(FundingCasePermissions::REVIEW_FINISH)) {
        return $this->createAmountLessThanZeroResult();
      }
    }

    if (round($new->getAmount(), 2) > round($current->getAmount(), 2)) {
      $amountDiff = $new->getAmount() - $current->getAmount();
      $amountAvailable = $this->payoutProcessManager->getAmountAvailable($payoutProcess);
      if (round($amountDiff, 2) > round($amountAvailable, 2)) {
        return $this->createAmountExceedsLimitResult();
      }
    }

    return EntityValidationResult::new();
  }

  /**
   * @inheritDoc
   *
   * @param \Civi\Funding\Entity\DrawdownEntity $new
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function validateNew(AbstractEntity $new, bool $checkPermissions): EntityValidationResult {
    $payoutProcess = $this->getPayoutProcess($new);
    $this->assertNotClosed($payoutProcess);

    $fundingCase = $this->getFundingCase($payoutProcess);
    if ($fundingCase->hasPermission('drawdown_create')) {
      // Only reviewers are allowed to create payback claims.
      if ($new->getAmount() < 0) {
        return $this->createAmountLessThanZeroResult();
      }
    }
    // Allow reviewers to create final drawdown.
    elseif (!$fundingCase->hasPermission(FundingCasePermissions::REVIEW_FINISH)) {
      throw new UnauthorizedException(E::ts('Permission to create drawdown is missing.'));
    }

    if (round($new->getAmount(), 2) > round($this->payoutProcessManager->getAmountAvailable($payoutProcess), 2)) {
      return $this->createAmountExceedsLimitResult();
    }

    return EntityValidationResult::new();
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertNotClosed(PayoutProcessEntity $payoutProcess): void {
    if ('closed' === $payoutProcess->getStatus()) {
      throw new UnauthorizedException(E::ts('Payout process is closed.'));
    }
  }

  private function createAmountLessThanZeroResult(): EntityValidationResult {
    return EntityValidationResult::new(EntityValidationError::new(
      'amount',
      E::ts('Requested amount is less than 0.')),
    );
  }

  private function createAmountExceedsLimitResult(): EntityValidationResult {
    return EntityValidationResult::new(EntityValidationError::new(
      'amount',
      E::ts('Requested amount is greater than available amount.')),
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFundingCase(PayoutProcessEntity $payoutProcess): FundingCaseEntity {
    $fundingCase = $this->fundingCaseManager->get($payoutProcess->getFundingCaseId());
    Assert::notNull($fundingCase);

    return $fundingCase;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getPayoutProcess(DrawdownEntity $drawdown): PayoutProcessEntity {
    $payoutProcess = $this->payoutProcessManager->get($drawdown->getPayoutProcessId());
    Assert::notNull($payoutProcess);

    return $payoutProcess;
  }

}
