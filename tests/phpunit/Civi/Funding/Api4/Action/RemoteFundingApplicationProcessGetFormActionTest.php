<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PropertyAnnotationInspection
 */
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Event\RemoteFundingApplicationProcessGetFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\RemoteFundingApplicationProcessGetFormAction
 * @covers \Civi\Funding\Event\RemoteFundingApplicationProcessGetFormEvent
 * @covers \Civi\Funding\Event\AbstractRemoteFundingGetFormEvent
 */
final class RemoteFundingApplicationProcessGetFormActionTest extends TestCase {

  private RemoteFundingApplicationProcessGetFormAction $action;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject&\Civi\Core\CiviEventDispatcher
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var array<string, mixed>
   */
  private array $applicationProcess;

  /**
   * @var array<string, mixed>
   */
  private array $fundingCase;

  /**
   * @var array<string, mixed>
   */
  private array $fundingCaseType;

  protected function setUp(): void {
    parent::setUp();
    $remoteFundingEntityManagerMock = $this->createMock(RemoteFundingEntityManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->action = new RemoteFundingApplicationProcessGetFormAction(
      $remoteFundingEntityManagerMock,
      $this->eventDispatcherMock
    );

    $this->action->setRemoteContactId('00');
    $this->action->setExtraParam('contactId', 11);
    $this->action->setApplicationProcessId(22);

    $this->applicationProcess = ['id' => 22, 'funding_case_id' => 33];
    $this->fundingCase = ['id' => 33, 'funding_case_type_id' => 44];
    $this->fundingCaseType = ['id' => 44];

    $remoteFundingEntityManagerMock->method('getById')->willReturnMap([
      ['FundingApplicationProcess', 22, '00', $this->applicationProcess],
      ['FundingCase', 33, '00', $this->fundingCase],
      ['FundingCaseType', 44, '00', $this->fundingCaseType],
    ]);
  }

  public function testRun(): void {
    $this->eventDispatcherMock->expects(static::exactly(3))
      ->method('dispatch')
      ->withConsecutive(
        [
          RemoteFundingApplicationProcessGetFormEvent::getEventName('RemoteFundingApplicationProcess', 'getForm'),
          static::callback(
            function (RemoteFundingApplicationProcessGetFormEvent $event): bool {
              static::assertSame(11, $event->getContactId());
              static::assertSame($this->applicationProcess, $event->getApplicationProcess());
              static::assertSame($this->fundingCase, $event->getFundingCase());
              static::assertSame($this->fundingCaseType, $event->getFundingCaseType());

              $event->setJsonSchema(['type' => 'object']);
              $event->setUiSchema(['type' => 'Group']);
              $event->setData(['applicationProcessId' => 22, 'foo' => 'bar']);

              return TRUE;
            }),
        ],
        [
          RemoteFundingApplicationProcessGetFormEvent::getEventName('RemoteFundingApplicationProcess'),
          static::isInstanceOf(RemoteFundingApplicationProcessGetFormEvent::class),
        ],
        [
          RemoteFundingApplicationProcessGetFormEvent::getEventName(),
          static::isInstanceOf(RemoteFundingApplicationProcessGetFormEvent::class),
        ]
      );

    $result = new Result();
    $this->action->_run($result);
    static::assertSame(1, $result->rowCount);
    static::assertSame([
      'jsonSchema' => ['type' => 'object'],
      'uiSchema' => ['type' => 'Group'],
      'data' => ['applicationProcessId' => 22, 'foo' => 'bar'],
    ], $result->getArrayCopy());
  }

  public function testNoEventListener(): void {
    static::expectExceptionObject(new \API_Exception(
      'Invalid applicationProcessId',
      'invalid_parameters'
    ));

    $result = new Result();
    $this->action->_run($result);
  }

}
