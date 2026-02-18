<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\Event\FundingCase\FundingCasePreUpdateEvent;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCaseWithdrawnSubscriber
 */
final class FundingCaseWithdrawnSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private FundingCaseWithdrawnSubscriber $subscriber;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $updateAmountApprovedHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->updateAmountApprovedHandlerMock = $this->createMock(FundingCaseUpdateAmountApprovedHandlerInterface::class);
    $this->subscriber = new FundingCaseWithdrawnSubscriber(
      $this->applicationProcessManagerMock,
      $this->updateAmountApprovedHandlerMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FundingCasePreUpdateEvent::class => 'onPreUpdate',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnPreUpdate(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 0.1,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::ONGOING,
      'amount_approved' => 0.1,
    ]);

    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($statusList);

    $this->updateAmountApprovedHandlerMock->expects(static::once())->method('handle')
      ->with((new FundingCaseUpdateAmountApprovedCommand(
        $fundingCaseBundle, 0.0, $statusList
      ))->setAuthorized(TRUE)
    );

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCaseBundle);
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateAmountNull(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => NULL,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::OPEN,
      'amount_approved' => NULL,
    ]);

    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCaseBundle);
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateAmountZero(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => NULL,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::ONGOING,
      'amount_approved' => 0.0,
    ]);

    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCaseBundle);
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateStatusNotChanged(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 2.0,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 2.0,
    ]);

    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCaseBundle);
    $this->subscriber->onPreUpdate($event);
  }

}
