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

use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Civi\Funding\Validation\EntityValidationError;
use Civi\Funding\Validation\EntityValidationResult;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * Checks that the drawdown amount is valid, i.e. does not exceed the amount
 * available and is not less than 0. 0 is allowed as amount to indicate that
 * (currently) no money is required.
 *
 * @implements ConcreteEntityValidatorInterface<DrawdownEntity>
 */
final class DrawdownAmountValidator implements ConcreteEntityValidatorInterface {

  private PayoutProcessManager $payoutProcessManager;

  /**
   * @inheritDoc
   */
  public static function getEntityClass(): string {
    return DrawdownEntity::class;
  }

  public function __construct(PayoutProcessManager $payoutProcessManager) {
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
  public function validate(AbstractEntity $new, AbstractEntity $current): EntityValidationResult {
    if ($new->getAmount() < 0) {
      return $this->createAmountLessThanZeroResult();
    }

    if ($new->getAmount() > $current->getAmount()) {
      $payoutProcess = $this->payoutProcessManager->get($new->getPayoutProcessId());
      Assert::notNull($payoutProcess);
      $amountDiff = $new->getAmount() - $current->getAmount();
      if ($amountDiff > $this->payoutProcessManager->getAmountAvailable($payoutProcess)) {
        return $this->createAmountExceedsLimitResult();
      }
    }

    return EntityValidationResult::new();
  }

  /**
   * @inheritDoc
   *
   * @param \Civi\Funding\Entity\DrawdownEntity $new
   */
  public function validateNew(AbstractEntity $new): EntityValidationResult {
    if ($new->getAmount() < 0) {
      return $this->createAmountLessThanZeroResult();
    }

    $payoutProcess = $this->payoutProcessManager->get($new->getPayoutProcessId());
    Assert::notNull($payoutProcess);

    if ($new->getAmount() > $this->payoutProcessManager->getAmountAvailable($payoutProcess)) {
      return $this->createAmountExceedsLimitResult();
    }

    return EntityValidationResult::new();
  }

  private function createAmountExceedsLimitResult(): EntityValidationResult {
    return EntityValidationResult::new(EntityValidationError::new(
      'amount',
      E::ts('Requested amount is greater than available amount.')),
    );
  }

  private function createAmountLessThanZeroResult(): EntityValidationResult {
    return EntityValidationResult::new(EntityValidationError::new(
      'amount',
      E::ts('Requested amount is less than 0.')),
    );
  }

}
