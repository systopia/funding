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
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingCaseType\MetaData\AutoUpdateAmountApproved;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\UpdateAmountApprovedSumOfAmountsEligibleSubscriber
 */
final class UpdateAmountApprovedSumOfAmountsEligibleSubscriberTest extends TestCase {

  private FundingCaseActionsDeterminerInterface&MockObject $actionsDeterminerMock;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private UpdateAmountApprovedSumOfAmountsEligibleSubscriber $subscriber;

  private FundingCaseUpdateAmountApprovedHandlerInterface&MockObject $updateAmountApprovedHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->metaDataMock = new FundingCaseTypeMetaDataMock();
    $this->metaDataMock->autoUpdateAmountApproved = AutoUpdateAmountApproved::SumOfAmountsEligible;
    $this->updateAmountApprovedHandlerMock = $this->createMock(FundingCaseUpdateAmountApprovedHandlerInterface::class);
    $this->subscriber = new UpdateAmountApprovedSumOfAmountsEligibleSubscriber(
      $this->actionsDeterminerMock,
      $this->applicationProcessManagerMock,
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      $this->updateAmountApprovedHandlerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testAmountEligibleChanged(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => TRUE, 'amount_eligible' => 12.34],
      ['status' => 'ongoing', 'amount_approved' => 100]
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
      'amount_eligible' => 12.33,
    ]);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();

    $statusList = [$applicationProcess->getId() => $applicationProcess->getFullStatus()];
    $this->applicationProcessManagerMock
      ->method('getStatusListByFundingCaseId')
      ->with($applicationProcessBundle->getFundingCase()->getId())
      ->willReturn($statusList);

    $this->actionsDeterminerMock
      ->expects(static::once())->method('isActionAllowed')
      ->with(
        FundingCaseActions::UPDATE_AMOUNT_APPROVED,
        new FundingCaseBundle(
          $fundingCase->withPermissions([FundingCasePermissions::AUTO_UPDATE_AMOUNT_APPROVED]),
          $applicationProcessBundle->getFundingCaseType(),
          $applicationProcessBundle->getFundingProgram(),
        ),
        $statusList
      )->willReturn(TRUE);

    $this->applicationProcessManagerMock
      ->method('getAmountEligibleByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn(333.33);

    $this->updateAmountApprovedHandlerMock
      ->expects(static::once())->method('handle')
      ->with(
        (new FundingCaseUpdateAmountApprovedCommand(
          $applicationProcessBundle,
          333.33,
          $statusList
        ))->setAuthorized(TRUE)
      );

    $this->subscriber->onUpdated(new ApplicationProcessUpdatedEvent(
      $previousApplicationProcess,
      $applicationProcessBundle
    ));
    $this->subscriber->onPreCommit();
  }

  public function testEligibilityChanged(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => TRUE, 'amount_eligible' => 12.34],
      ['status' => 'ongoing', 'amount_approved' => 100]
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => NULL,
      'amount_eligible' => 12.34,
    ]);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();

    $statusList = [$applicationProcess->getId() => $applicationProcess->getFullStatus()];
    $this->applicationProcessManagerMock
      ->method('getStatusListByFundingCaseId')
      ->with($applicationProcessBundle->getFundingCase()->getId())
      ->willReturn($statusList);

    $this->actionsDeterminerMock
      ->expects(static::once())->method('isActionAllowed')
      ->with(
        FundingCaseActions::UPDATE_AMOUNT_APPROVED,
        new FundingCaseBundle(
          $fundingCase->withPermissions([FundingCasePermissions::AUTO_UPDATE_AMOUNT_APPROVED]),
          $applicationProcessBundle->getFundingCaseType(),
          $applicationProcessBundle->getFundingProgram(),
        ),
        $statusList
      )->willReturn(TRUE);

    $this->applicationProcessManagerMock
      ->method('getAmountEligibleByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn(333.33);

    $this->updateAmountApprovedHandlerMock
      ->expects(static::once())->method('handle')
      ->with(
        (new FundingCaseUpdateAmountApprovedCommand(
          $applicationProcessBundle,
          333.33,
          $statusList
        ))->setAuthorized(TRUE)
      );

    $this->subscriber->onUpdated(new ApplicationProcessUpdatedEvent(
      $previousApplicationProcess,
      $applicationProcessBundle
    ));
    $this->subscriber->onPreCommit();
  }

  public function testAutoUpdateNotEnabled(): void {
    $this->metaDataMock->autoUpdateAmountApproved = AutoUpdateAmountApproved::No;
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => TRUE, 'amount_eligible' => 12.34],
      ['status' => 'ongoing', 'amount_approved' => 100]
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
      'amount_eligible' => 12.33,
    ]);

    $this->applicationProcessManagerMock->expects(static::never())->method('getStatusListByFundingCaseId');
    $this->actionsDeterminerMock->expects(static::never())->method('isActionAllowed');
    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $this->subscriber->onUpdated(new ApplicationProcessUpdatedEvent(
      $previousApplicationProcess,
      $applicationProcessBundle
    ));
    $this->subscriber->onPreCommit();
  }

  public function testFundingCaseNotOngoing(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['is_eligible' => TRUE, 'amount_eligible' => 12.34],
      ['status' => 'ongoing', 'amount_approved' => 100]
    );
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
      'amount_eligible' => 12.34,
    ]);

    $this->applicationProcessManagerMock->expects(static::never())->method('getStatusListByFundingCaseId');
    $this->actionsDeterminerMock->expects(static::never())->method('isActionAllowed');
    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $this->subscriber->onUpdated(new ApplicationProcessUpdatedEvent(
      $previousApplicationProcess,
      $applicationProcessBundle
    ));
    $this->subscriber->onPreCommit();
  }

}
