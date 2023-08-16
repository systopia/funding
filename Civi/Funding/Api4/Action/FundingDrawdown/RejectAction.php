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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

class RejectAction extends AbstractAction {

  use IdParameterTrait;

  private DrawdownManager $drawdownManager;

  private FundingCaseManager $fundingCaseManager;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(
    DrawdownManager $drawdownManager,
    FundingCaseManager $fundingCaseManager,
    PayoutProcessManager $payoutProcessManager
  ) {
    parent::__construct(FundingDrawdown::_getEntityName(), 'reject');
    $this->drawdownManager = $drawdownManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $drawdown = $this->drawdownManager->get($this->getId());
    Assert::notNull($drawdown, sprintf('Drawdown with ID "%d" not found', $this->getId()));
    $payoutProcess = $this->payoutProcessManager->get($drawdown->getPayoutProcessId());
    Assert::notNull($payoutProcess, sprintf('Payout process with ID "%d" not found', $drawdown->getPayoutProcessId()));
    $fundingCase = $this->fundingCaseManager->get($payoutProcess->getFundingCaseId());
    Assert::notNull($fundingCase, sprintf('Funding case with ID "%d" not found', $payoutProcess->getFundingCaseId()));

    if (!in_array('review_drawdown', $fundingCase->getPermissions(), TRUE)) {
      throw new UnauthorizedException(E::ts('Permission to reject drawdown is missing.'));
    }

    $this->drawdownManager->delete($drawdown);

    $result->exchangeArray([$drawdown->toArray()]);
  }

}
