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
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFinishClearingCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseFinishClearingCommand
 */
final class FundingCaseFinishClearingHandlerTest extends TestCase {

  private FundingCaseActionsDeterminerInterface&MockObject $actionsDeterminerMock;

  private DrawdownManager&MockObject $drawdownManagerMock;

  private FundingCaseManager&MockObject $fundingCaseManagerMock;

  private FundingCaseFinishClearingHandler $handler;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private PayoutProcessManager&MockObject $payoutProcessManagerMock;

  private RequestContextInterface&MockObject $requestContextMock;

  private FundingCaseStatusDeterminerInterface&MockObject $statusDeterminerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(strtotime('2024-07-15 10:20:30'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->metaDataMock = new FundingCaseTypeMetaDataMock();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->requestContextMock = $this->createMock(RequestContextInterface::class);
    $this->statusDeterminerMock = $this->createMock(FundingCaseStatusDeterminerInterface::class);
    $this->handler = new FundingCaseFinishClearingHandler(
      $this->actionsDeterminerMock,
      $this->drawdownManagerMock,
      $this->fundingCaseManagerMock,
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      $this->payoutProcessManagerMock,
      $this->requestContextMock,
      $this->statusDeterminerMock
    );

    $this->requestContextMock->method('getContactId')->willReturn(23);
  }

  /**
   * @dataProvider provideFinalDrawdownAccepted
   */
  public function testHandle(bool $finalDrawdownAccepted): void {
    $this->metaDataMock->finalDrawdownAcceptedByDefault = $finalDrawdownAccepted;

    $fundingCase = FundingCaseFactory::createFundingCase(['status' => 'ongoing']);
    $applicationProcessStatusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(FundingCaseActions::FINISH_CLEARING, 'ongoing', $applicationProcessStatusList)
      ->willReturn(TRUE);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('getLastByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($payoutProcess);

    $this->fundingCaseManagerMock->method('getAmountRemaining')
      ->with($fundingCase->getId())
      ->willReturn(-1.23);
    $this->drawdownManagerMock->expects(static::once())->method('deleteNewDrawdownsByPayoutProcessId');
    $drawdown = DrawdownFactory::create([
      'id' => NULL,
      'amount' => -1.23,
      'creation_date' => '2024-07-15 10:20:30',
      'requester_contact_id' => 23,
    ]);
    $this->drawdownManagerMock->expects(static::once())->method('insert')->with($drawdown);
    if ($finalDrawdownAccepted) {
      $this->drawdownManagerMock->expects(static::once())->method('accept')->with($drawdown);
    }
    else {
      $this->drawdownManagerMock->expects(static::never())->method('accept');
    }

    $this->payoutProcessManagerMock->expects(static::once())->method('close')
      ->with($payoutProcess);

    $this->statusDeterminerMock->method('getStatus')
      ->with($fundingCase->getStatus(), FundingCaseActions::FINISH_CLEARING)
      ->willReturn('new-status');
    $this->fundingCaseManagerMock->expects(static::once())->method('update')
      ->with($fundingCase);

    $this->handler->handle(new FundingCaseFinishClearingCommand(
      $fundingCase,
      $applicationProcessStatusList,
      $fundingCaseType,
      $fundingProgram
    ));

    static::assertSame('new-status', $fundingCase->getStatus());
  }

  /**
   * @phpstan-return iterable<array{bool}>
   */
  public function provideFinalDrawdownAccepted(): iterable {
    yield [TRUE];
    yield [FALSE];
  }

  public function testHandleAmountRemainingZero(): void {
    $fundingCase = FundingCaseFactory::createFundingCase(['status' => 'ongoing']);
    $applicationProcessStatusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(FundingCaseActions::FINISH_CLEARING, 'ongoing', $applicationProcessStatusList)
      ->willReturn(TRUE);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('getLastByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($payoutProcess);

    $this->fundingCaseManagerMock->method('getAmountRemaining')
      ->with($fundingCase->getId())
      ->willReturn(0.0);
    $this->drawdownManagerMock->expects(static::once())->method('deleteNewDrawdownsByPayoutProcessId');
    $this->drawdownManagerMock->expects(static::never())->method('insert');

    $this->payoutProcessManagerMock->expects(static::once())->method('close')
      ->with($payoutProcess);

    $this->statusDeterminerMock->method('getStatus')
      ->with($fundingCase->getStatus(), FundingCaseActions::FINISH_CLEARING)
      ->willReturn('new-status');
    $this->fundingCaseManagerMock->expects(static::once())->method('update')
      ->with($fundingCase);

    $this->handler->handle(new FundingCaseFinishClearingCommand(
      $fundingCase,
      $applicationProcessStatusList,
      $fundingCaseType,
      $fundingProgram
    ));

    static::assertSame('new-status', $fundingCase->getStatus());
  }

  public function testHandleActionNotAllowed(): void {
    $fundingCase = FundingCaseFactory::createFundingCase(['status' => 'ongoing', 'identifier' => 'test']);
    $applicationProcessStatusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(FundingCaseActions::FINISH_CLEARING, 'ongoing', $applicationProcessStatusList)
      ->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Finishing the clearing of funding case "test" is not allowed.');

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    $this->handler->handle(new FundingCaseFinishClearingCommand(
      $fundingCase,
      $applicationProcessStatusList,
      $fundingCaseType,
      $fundingProgram
    ));

    static::assertSame('new--status', $fundingCase->getStatus());
  }

}
