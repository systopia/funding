<?php
declare(strict_types = 1);

namespace Civi\Funding\Notification;

use Civi\Api4\Generic\Result;
use Civi\Api4\MessageTemplate;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Notification\NotificationWorkflowDeterminer
 */
final class NotificationWorkflowDeterminerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private $api4Mock;

  /**
   * @var \Civi\Funding\Notification\NotificationWorkflowDeterminer
   */
  private NotificationWorkflowDeterminer $workflowDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->workflowDeterminer = new NotificationWorkflowDeterminer($this->api4Mock);
  }

  public function testGetWorkflowNameWithCaseType(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'FCT']);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(MessageTemplate::getEntityName(), 'get', [
        'select' => ['workflow_name', 'is_active'],
        'where' => [['workflow_name', '=', 'funding.case_type:FCT.test']],
      ])->willReturn(new Result([['workflow_name' => 'funding.case_type:FCT.test', 'is_active' => TRUE]]));

    static::assertSame(
      'funding.case_type:FCT.test',
      $this->workflowDeterminer->getWorkflowName('test', $fundingCaseType)
    );
  }

  public function testGetWorkflowNameWithCaseTypeDisabled(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'FCT']);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(MessageTemplate::getEntityName(), 'get', [
        'select' => ['workflow_name', 'is_active'],
        'where' => [['workflow_name', '=', 'funding.case_type:FCT.test']],
      ])->willReturn(new Result([['workflow_name' => 'funding.case_type:FCT.test', 'is_active' => FALSE]]));

    static::assertNull($this->workflowDeterminer->getWorkflowName('test', $fundingCaseType));
  }

  public function testGetWorkflowNameWithoutCaseType(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'FCT']);

    $series = [
      [
        [
          MessageTemplate::getEntityName(),
          'get',
          [
            'select' => ['workflow_name', 'is_active'],
            'where' => [['workflow_name', '=', 'funding.case_type:FCT.test']],
          ],
        ],
        new Result([]),
      ],
      [
        [
          MessageTemplate::getEntityName(),
          'get',
          [
            'select' => ['workflow_name', 'is_active'],
            'where' => [['workflow_name', '=', 'funding.test']],
          ],
        ],
        new Result([['workflow_name' => 'funding.test', 'is_active' => TRUE]]),
      ],
    ];
    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->willReturnCallback(function (...$args) use (&$series) {
        // @phpstan-ignore-next-line
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    static::assertSame(
      'funding.test',
      $this->workflowDeterminer->getWorkflowName('test', $fundingCaseType)
    );
  }

  public function testGetWorkflowNameWithoutCaseTypeDisabled(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'FCT']);

    $series = [
      [
        [
          MessageTemplate::getEntityName(),
          'get',
          [
            'select' => ['workflow_name', 'is_active'],
            'where' => [['workflow_name', '=', 'funding.case_type:FCT.test']],
          ],
        ],
        new Result([]),
      ],
      [
        [
          MessageTemplate::getEntityName(),
          'get',
          [
            'select' => ['workflow_name', 'is_active'],
            'where' => [['workflow_name', '=', 'funding.test']],
          ],
        ],
        new Result([['workflow_name' => 'funding.test', 'is_active' => FALSE]]),
      ],
    ];
    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->willReturnCallback(function (...$args) use (&$series) {
        // @phpstan-ignore-next-line
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    static::assertNull($this->workflowDeterminer->getWorkflowName('test', $fundingCaseType));
  }

  public function testGetWorkflowNameNone(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType(['name' => 'FCT']);

    $series = [
      [
        [
          MessageTemplate::getEntityName(),
          'get',
          [
            'select' => ['workflow_name', 'is_active'],
            'where' => [['workflow_name', '=', 'funding.case_type:FCT.test']],
          ],
        ],
        new Result([]),
      ],
      [
        [
          MessageTemplate::getEntityName(),
          'get',
          [
            'select' => ['workflow_name', 'is_active'],
            'where' => [['workflow_name', '=', 'funding.test']],
          ],
        ],
        new Result([]),
      ],
    ];
    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->willReturnCallback(function (...$args) use (&$series) {
        // @phpstan-ignore-next-line
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    static::assertNull($this->workflowDeterminer->getWorkflowName('test', $fundingCaseType));
  }

}
