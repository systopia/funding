<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFinishClearingCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\RequestContext\RequestContext;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class FundingCaseFinishClearingHandler implements FundingCaseFinishClearingHandlerInterface {

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  private DrawdownManager $drawdownManager;

  private FundingCaseManager $fundingCaseManager;

  private PayoutProcessManager $payoutProcessManager;

  private RequestContextInterface $requestContext;

  private FundingCaseStatusDeterminerInterface $statusDeterminer;

  public function __construct(
    FundingCaseActionsDeterminerInterface $actionsDeterminer,
    DrawdownManager $drawdownManager,
    FundingCaseManager $fundingCaseManager,
    PayoutProcessManager $payoutProcessManager,
    RequestContextInterface $requestContext,
    FundingCaseStatusDeterminerInterface $statusDeterminer
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->drawdownManager = $drawdownManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->payoutProcessManager = $payoutProcessManager;
    $this->requestContext = $requestContext;
    $this->statusDeterminer = $statusDeterminer;
  }

  /**
   * @throws \Civi\Funding\Exception\FundingException
   * @throws \CRM_Core_Exception
   */
  public function handle(FundingCaseFinishClearingCommand $command): void {
    $fundingCase = $command->getFundingCase();
    $this->assertAuthorized($command);

    $payoutProcess = $this->payoutProcessManager->getLastByFundingCaseId($fundingCase->getId());
    Assert::notNull($payoutProcess);
    $this->drawdownManager->deleteNewDrawdownsByPayoutProcessId($payoutProcess->getId());

    $amountRemaining = $this->fundingCaseManager->getAmountRemaining($fundingCase->getId());
    if (0.0 !== $amountRemaining) {
      $drawdown = DrawdownEntity::fromArray([
        'payout_process_id' => $payoutProcess->getId(),
        'status' => 'new',
        'creation_date' => date('Y-m-d H:i:s'),
        'amount' => $amountRemaining,
        'acception_date' => NULL,
        'requester_contact_id' => $this->requestContext->getContactId(),
        'reviewer_contact_id' => NULL,
      ]);
      $this->drawdownManager->insert($drawdown);
      $this->drawdownManager->accept($drawdown, $this->requestContext->getContactId());
    }

    $this->payoutProcessManager->close($payoutProcess);

    $fundingCase->setStatus($this->statusDeterminer->getStatus(
      $fundingCase->getStatus(),
      FundingCaseActions::FINISH_CLEARING
    ));
    $this->fundingCaseManager->update($fundingCase);
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertAuthorized(FundingCaseFinishClearingCommand $command): void {
    if (!$this->actionsDeterminer->isActionAllowed(
      FundingCaseActions::FINISH_CLEARING,
      $command->getFundingCase()->getStatus(),
      $command->getApplicationProcessStatusList(),
      $command->getFundingCase()->getPermissions(),
    )) {
      throw new UnauthorizedException(E::ts('Finishing the clearing of this funding case is not allowed.'));
    }
  }

}
