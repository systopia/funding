<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\AutoUpdateAmountApproved;
use Civi\Funding\Util\FloatUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Update amount approved according to
 * {@link AutoUpdateAmountApproved::SumOfAmountsEligible}.
 *
 * Changes are done in pre commit in case multiple application processes of the
 * same funding case are changed within the same transaction.
 *
 * @see \Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface::getAutoUpdateAmountApproved()
 */
class UpdateAmountApprovedSumOfAmountsEligibleSubscriber implements EventSubscriberInterface {

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  /**
   * @var array<int, \Civi\Funding\Entity\FundingCaseBundle>
   */
  private array $fundingCaseBundles = [];

  private FundingCaseUpdateAmountApprovedHandlerInterface $updateAmountApprovedHandler;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function __construct(
    FundingCaseActionsDeterminerInterface $actionsDeterminer,
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
    FundingCaseUpdateAmountApprovedHandlerInterface $updateAmountApprovedHandler
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->metaDataProvider = $metaDataProvider;
    $this->updateAmountApprovedHandler = $updateAmountApprovedHandler;
  }

  /**
   * @codeCoverageIgnore
   */
  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    // This could be part of onUpdate(), though it prevent unit test, i.e.
    // without booted CiviCRM environment.
    \CRM_Core_Transaction::addCallback(
      \CRM_Core_Transaction::PHASE_PRE_COMMIT,
      [$this, 'onPreCommit'],
      NULL,
      self::class . '::onPreCommit'
    );

    \CRM_Core_Transaction::addCallback(
      \CRM_Core_Transaction::PHASE_PRE_ROLLBACK,
      fn () => $this->fundingCaseBundles = [],
      NULL,
      self::class . '::onPreRollback'
    );
  }

  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $fundingCase = $event->getFundingCase();
    $applicationProcess = $event->getApplicationProcess();
    $previousApplicationProcess = $event->getPreviousApplicationProcess();

    if (FundingCaseStatus::ONGOING !== $fundingCase->getStatus()
      || (FloatUtil::isMoneyEqual(
        $applicationProcess->getAmountEligible(), $previousApplicationProcess->getAmountEligible()
      ) && $applicationProcess->getIsEligible() === $previousApplicationProcess->getIsEligible())
    ) {
      return;
    }

    $metaData = $this->metaDataProvider->get($event->getFundingCaseType()->getName());
    if (AutoUpdateAmountApproved::SumOfAmountsEligible === $metaData->getAutoUpdateAmountApproved()) {
      $this->fundingCaseBundles[$fundingCase->getId()] = $event->getApplicationProcessBundle();
    }

  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreCommit(): void {
    try {
      foreach ($this->fundingCaseBundles as $fundingCaseBundle) {
        $this->changeAmountApprovedToSumAmountEligible($fundingCaseBundle);
      }
    }
    finally {
      $this->fundingCaseBundles = [];
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function changeAmountApprovedToSumAmountEligible(FundingCaseBundle $fundingCaseBundle): void {
    $fundingCase = $fundingCaseBundle->getFundingCase();
    assert(NULL !== $fundingCase->getAmountApproved());
    $fundingCaseType = $fundingCaseBundle->getFundingCaseType();
    $fundingProgram = $fundingCaseBundle->getFundingProgram();

    $statusList = $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId());

    if ($this->actionsDeterminer->isActionAllowed(
      FundingCaseActions::UPDATE_AMOUNT_APPROVED,
      new FundingCaseBundle(
        $fundingCase->withPermissions([FundingCasePermissions::AUTO_UPDATE_AMOUNT_APPROVED]),
        $fundingCaseType,
        $fundingProgram
      ),
      $statusList
    )) {
      $amountEligible = $this->applicationProcessManager->getAmountEligibleByFundingCaseId($fundingCase->getId());
      if (!FloatUtil::isMoneyEqual($amountEligible, $fundingCase->getAmountApproved())) {
        $this->updateAmountApprovedHandler->handle(
          (new FundingCaseUpdateAmountApprovedCommand(
            $fundingCaseBundle,
            $amountEligible,
            $statusList
          ))->setAuthorized(TRUE)
        );
      }
    }
  }

}
