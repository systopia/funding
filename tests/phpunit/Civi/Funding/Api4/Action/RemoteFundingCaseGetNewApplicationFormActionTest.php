<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Event\RemoteFundingCaseGetNewApplicationFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\RemoteFundingCaseGetNewApplicationFormAction
 * @covers \Civi\Funding\Event\RemoteFundingCaseGetNewApplicationFormEvent
 */
final class RemoteFundingCaseGetNewApplicationFormActionTest extends TestCase {

  private RemoteFundingCaseGetNewApplicationFormAction $action;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var array<string, mixed>
   */
  private array $fundingCaseType;

  /**
   * @var array<string, mixed>
   */
  private array $fundingProgram;

  protected function setUp(): void {
    parent::setUp();
    $remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->action = new RemoteFundingCaseGetNewApplicationFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setFundingCaseTypeId(22);
    $this->action->setFundingProgramId(33);

    $this->fundingCaseType = ['id' => 22];
    $this->fundingProgram = ['id' => 33];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingCaseType', 22, '00', $this->fundingCaseType],
      ['FundingProgram', 33, '00', $this->fundingProgram],
    ]);
  }

  public function testRun(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingCaseGetNewApplicationFormEvent::getEventName('RemoteFundingCase', 'getNewApplicationForm'),
          static::callback(
            function (RemoteFundingCaseGetNewApplicationFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());
              static::assertSame($this->fundingProgram, $event->getFundingProgram());

              $event->setJsonSchema(['type' => 'object']);
              $event->setUiSchema(['type' => 'Group']);
              $event->setData(['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          RemoteFundingCaseGetNewApplicationFormEvent::getEventName('RemoteFundingCase'),
          static::isInstanceOf(RemoteFundingCaseGetNewApplicationFormEvent::class),
        ],
        [
          RemoteFundingCaseGetNewApplicationFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingCaseGetNewApplicationFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'jsonSchema' => ['type' => 'object'],
      'uiSchema' => ['type' => 'Group'],
      'data' => ['fundingCaseTypeId' => 22, 'fundingProgramId' => 33, 'foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testNoEventListener(): void {
    static::expectExceptionObject(new \API_Exception(
      'Invalid fundingProgramId or fundingCaseTypeId',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
