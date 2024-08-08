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

namespace Civi\Funding\FundingCase\Handler\Helper;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\Helper\ApplicationAllowedActionApplier
 */
final class ApplicationAllowedActionApplierTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\Handler\Helper\ApplicationAllowedActionApplier
   */
  private ApplicationAllowedActionApplier $actionApplier;

  /**
   * @var \Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionApplyHandlerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessBundleLoaderMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionApplyHandlerMock = $this->createMock(ApplicationActionApplyHandlerInterface::class);
    $this->actionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->actionApplier = new ApplicationAllowedActionApplier(
      $this->actionApplyHandlerMock,
      $this->actionsDeterminerMock,
      $this->applicationProcessBundleLoaderMock,
    );
  }

  public function testApplyAllowedAction(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $statusList = [123 => new FullApplicationProcessStatus('test', NULL, NULL)];
    $this->applicationProcessBundleLoaderMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($statusList);
    $this->actionsDeterminerMock->method('isActionAllowed')->with(
      'action',
      $applicationProcessBundle,
      $statusList
    )->willReturn(TRUE);

    $contactId = 2;
    $this->actionApplyHandlerMock->expects(static::once())->method('handle')->with(new ApplicationActionApplyCommand(
      $contactId, 'action', $applicationProcessBundle, NULL
    ));

    $this->actionApplier->applyAllowedAction($contactId, $applicationProcessBundle, 'action');
  }

  public function testApplyAllowedActionNotAllowed(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $statusList = [123 => new FullApplicationProcessStatus('test', NULL, NULL)];
    $this->applicationProcessBundleLoaderMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($statusList);
    $this->actionsDeterminerMock->method('isActionAllowed')->with(
      'action',
      $applicationProcessBundle,
      $statusList
    )->willReturn(FALSE);

    $contactId = 2;
    $this->actionApplyHandlerMock->expects(static::never())->method('handle');

    $this->actionApplier->applyAllowedAction($contactId, $applicationProcessBundle, 'action');
  }

  public function testApplyAllowedActionsByFundingCase(): void {
    $applicationProcessBundle1 = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'id' => 10,
      'status' => 'status1',
    ]);
    $applicationProcess1 = $applicationProcessBundle1->getApplicationProcess();
    $applicationProcessBundle2 = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'id' => 20,
      'status' => 'status2',
    ]);
    $applicationProcess2 = $applicationProcessBundle2->getApplicationProcess();
    $fundingCase = $applicationProcessBundle1->getFundingCase();

    $this->applicationProcessBundleLoaderMock->method('getByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn([$applicationProcessBundle1, $applicationProcessBundle2]);

    $this->applicationProcessBundleLoaderMock->method('getStatusList')->willReturnMap([
      [
        $applicationProcessBundle1,
        [$applicationProcess2->getId() => $applicationProcess2->getFullStatus()],
      ],
      [
        $applicationProcessBundle2,
        [$applicationProcess1->getId() => $applicationProcess1->getFullStatus()],
      ],
    ]);

    $this->actionsDeterminerMock->method('isActionAllowed')->willReturnCallback(
      function (string $action, ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList)
      use ($fundingCase) {
        static::assertSame('action', $action);
        static::assertEquals($fundingCase, $applicationProcessBundle->getFundingCase());
        if ('status1' === $applicationProcessBundle->getApplicationProcess()->getStatus()) {
          static::assertEquals([20 => new FullApplicationProcessStatus('status2', NULL, NULL)], $statusList);

          return FALSE;
        }

        static::assertSame('status2', $applicationProcessBundle->getApplicationProcess()->getStatus());
        static::assertEquals([10 => new FullApplicationProcessStatus('status1', NULL, NULL)], $statusList);

        return TRUE;
      }
    );

    $contactId = 2;
    $this->actionApplyHandlerMock->expects(static::once())->method('handle')->with(new ApplicationActionApplyCommand(
      $contactId, 'action', $applicationProcessBundle2, NULL
    ));

    $this->actionApplier->applyAllowedActionsByFundingCase($contactId, $fundingCase, 'action');
  }

}
