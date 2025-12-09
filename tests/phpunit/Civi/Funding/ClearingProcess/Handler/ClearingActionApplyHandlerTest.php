<?php
declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingStatusDeterminer;
use Civi\Funding\ClearingProcess\Command\ClearingActionApplyCommand;
use Civi\Funding\Entity\FullClearingProcessStatus;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Handler\ClearingActionApplyHandler
 */
final class ClearingActionApplyHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingActionsDeterminer&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  private ClearingProcessManager&MockObject $clearingProcessManagerMock;


  private ClearingActionApplyHandler $handler;

  private ClearingStatusDeterminer&MockObject $statusDeterminerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ClearingActionsDeterminer::class);
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->statusDeterminerMock = $this->createMock(ClearingStatusDeterminer::class);
    $this->handler = new ClearingActionApplyHandler(
      $this->actionsDeterminerMock,
      $this->clearingProcessManagerMock,
      $this->statusDeterminerMock
    );
  }

  public function testHandle(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $action = 'test';
    $newFullStatus = new FullClearingProcessStatus('new', TRUE, TRUE);

    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with($action, $clearingProcessBundle)
      ->willReturn(TRUE);

    $this->statusDeterminerMock->method('getStatus')
      ->with($clearingProcessBundle->getClearingProcess()->getFullStatus(), $action)
      ->willReturn($newFullStatus);

    $this->clearingProcessManagerMock->expects(static::once())->method('update')
      ->with($clearingProcessBundle);

    $this->handler->handle(new ClearingActionApplyCommand($clearingProcessBundle, $action));
    static::assertEquals($newFullStatus, $clearingProcessBundle->getClearingProcess()->getFullStatus());
  }

  public function testHandleActionNotAllowed(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['id' => 123]);
    $action = 'test';

    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with($action, $clearingProcessBundle)
      ->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Action "test" is not allowed on clearing process with ID 123');
    $this->handler->handle(new ClearingActionApplyCommand($clearingProcessBundle, $action));
  }

}
