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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\FundingCase\FundingCasePreUpdateEvent;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the amount approved to 0.0 if a funding case status changes to
 * withdrawn and there's an amount approved.
 */
class FundingCaseWithdrawnSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private FundingCaseUpdateAmountApprovedHandlerInterface $updateAmountApprovedHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingCasePreUpdateEvent::class => 'onPreUpdate'];
  }

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    FundingCaseUpdateAmountApprovedHandlerInterface $updateAmountApprovedHandler
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->updateAmountApprovedHandler = $updateAmountApprovedHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreUpdate(FundingCasePreUpdateEvent $event): void {
    $fundingCase = $event->getFundingCase();
    if ($this->isStatusChangedToWithdrawn($fundingCase, $event->getPreviousFundingCase())) {
      if ($fundingCase->getAmountApproved() > 0) {
        $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
        assert(NULL !== $fundingCaseType);
        $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
        assert(NULL !== $fundingProgram);

        $this->updateAmountApprovedHandler->handle((new FundingCaseUpdateAmountApprovedCommand(
          $fundingCase,
          0.0,
          $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId()),
          $fundingCaseType,
          $fundingProgram
        ))->setAuthorized(TRUE));
      }
    }
  }

  private function isStatusChangedToWithdrawn(
    FundingCaseEntity $fundingCase,
    FundingCaseEntity $previousFundingCase
  ): bool {
    return $fundingCase->getStatus() !== $previousFundingCase->getStatus()
      && FundingCaseStatus::WITHDRAWN === $fundingCase->getStatus();
  }

}
