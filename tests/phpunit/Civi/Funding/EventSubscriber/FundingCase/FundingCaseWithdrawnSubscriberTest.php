<?php
declare(strict_types = 1);

namespace tests\phpunit\Civi\Funding\EventSubscriber\FundingCase;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\FundingCase\FundingCasePreUpdateEvent;
use Civi\Funding\EventSubscriber\FundingCase\FundingCaseWithdrawnSubscriber;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
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

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  private FundingCaseWithdrawnSubscriber $subscriber;

  /**
   * @var \Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $updateAmountApprovedHandlerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->updateAmountApprovedHandlerMock = $this->createMock(FundingCaseUpdateAmountApprovedHandlerInterface::class);
    $this->subscriber = new FundingCaseWithdrawnSubscriber(
      $this->applicationProcessManagerMock,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
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
    $fundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 0.1,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::ONGOING,
      'amount_approved' => 0.1,
    ]);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingProgramManagerMock->method('get')->with($fundingProgram->getId())->willReturn($fundingProgram);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $this->fundingCaseTypeManagerMock->method('get')->with($fundingCaseType->getId())->willReturn($fundingCaseType);

    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->applicationProcessManagerMock->method('getStatusListByFundingCaseId')
      ->with($fundingCase->getId())
      ->willReturn($statusList);

    $this->updateAmountApprovedHandlerMock->expects(static::once())->method('handle')
      ->with((new FundingCaseUpdateAmountApprovedCommand(
        $fundingCase, 0.0, $statusList, $fundingCaseType, $fundingProgram
      ))->setAuthorized(TRUE)
    );

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCase);
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateAmountNull(): void {
    $fundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => NULL,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::OPEN,
      'amount_approved' => NULL,
    ]);

    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCase);
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateAmountZero(): void {
    $fundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => NULL,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::ONGOING,
      'amount_approved' => 0.0,
    ]);

    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCase);
    $this->subscriber->onPreUpdate($event);
  }

  public function testOnPreUpdateStatusNotChanged(): void {
    $fundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 2.0,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 2.0,
    ]);

    $this->updateAmountApprovedHandlerMock->expects(static::never())->method('handle');

    $event = new FundingCasePreUpdateEvent($previousFundingCase, $fundingCase);
    $this->subscriber->onPreUpdate($event);
  }

}
