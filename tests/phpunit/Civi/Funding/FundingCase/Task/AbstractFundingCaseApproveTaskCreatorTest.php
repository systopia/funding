<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Task;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Task\AbstractFundingCaseApproveTaskCreator
 */
final class AbstractFundingCaseApproveTaskCreatorTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private AbstractFundingCaseApproveTaskCreator $taskCreator;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->taskCreator = $this->getMockForAbstractClass(
      AbstractFundingCaseApproveTaskCreator::class,
      [$this->api4Mock]
    );
  }

  public function testCreateTasksOnChangedToEligible(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_eligible' => TRUE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => FALSE,
    ]);

    $this->api4Mock->expects(static::once())->method('countEntities')
      ->with(FundingApplicationProcess::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'is_eligible' => NULL,
      ]))->willReturn(0);

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Approve Funding Case',
        'affected_identifier' => $applicationProcessBundle->getFundingCase()->getIdentifier(),
        'required_permissions' => ['review_calculative', 'review_content'],
        'type' => 'approve',
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      ]),
    ], [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]);
  }

  public function testCreateTasksOnChangedToEligibleWithUndecidedEligiblity(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_eligible' => TRUE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => FALSE,
    ]);

    $this->api4Mock->expects(static::once())->method('countEntities')
      ->with(FundingApplicationProcess::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'is_eligible' => NULL,
      ]))->willReturn(1);

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

  public function testCreateTasksOnChangedStillEligible(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'is_eligible' => TRUE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
    ]);

    $this->api4Mock->expects(static::never())->method('countEntities');

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($applicationProcessBundle, $previousApplicationProcess)]
    );
  }

}
