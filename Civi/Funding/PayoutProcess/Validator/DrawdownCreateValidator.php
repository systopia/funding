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
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Civi\Funding\Validation\EntityValidationResult;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * Checks if user has permission to create drawdown.
 *
 * @implements ConcreteEntityValidatorInterface<DrawdownEntity>
 */
final class DrawdownCreateValidator implements ConcreteEntityValidatorInterface {

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
  public function validate(AbstractEntity $new, AbstractEntity $current): EntityValidationResult {
    return EntityValidationResult::new();
  }

  /**
   * @inheritDoc
   *
   * @param \Civi\Funding\Entity\DrawdownEntity $new
   */
  public function validateNew(AbstractEntity $new): EntityValidationResult {
    $fundingCase = $this->getFundingCase($new);
    $this->assertPermission($fundingCase);

    return EntityValidationResult::new();
  }

  private function assertPermission(FundingCaseEntity $fundingCase): void {
    if (!$fundingCase->hasPermission('drawdown_create')) {
      throw new UnauthorizedException(E::ts('Permission to create drawdown is missing.'));
    }
  }

  private function getFundingCase(DrawdownEntity $drawdown): FundingCaseEntity {
    $payoutProcess = $this->payoutProcessManager->get($drawdown->getPayoutProcessId());
    Assert::notNull($payoutProcess);
    $fundingCase = $this->fundingCaseManager->get($payoutProcess->getFundingCaseId());
    Assert::notNull($fundingCase);

    return $fundingCase;
  }

}
