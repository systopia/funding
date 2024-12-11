<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Task\AbstractCombinedApplicationApplyTaskModifier
 */
final class AbstractCombinedApplicationApplyTaskModifierTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private AbstractCombinedApplicationApplyTaskModifier $taskModifier;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->taskModifier = $this->getMockForAbstractClass(AbstractCombinedApplicationApplyTaskModifier::class, [
      $this->applicationProcessManagerMock,
    ]);
  }

  public function testModifyTaskWithAppliableApplication(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['modification_date' => '2000-01-02 03:04:05']);
    $previousFundingCase = FundingCaseFactory::createFundingCase(['modification_date' => '2000-01-01 01:01:01']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and Apply Application',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_apply'],
      'type' => 'apply',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->applicationProcessManagerMock->method('countBy')
      ->with(CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
        'status' => ['draft', 'new', 'rework'],
      ]))->willReturn(1);

    static::assertFalse($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskWithoutAppliableApplication(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['modification_date' => '2000-01-02 03:04:05']);
    $previousFundingCase = FundingCaseFactory::createFundingCase(['modification_date' => '2000-01-01 01:01:01']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and Apply Application',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_apply'],
      'type' => 'apply',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->applicationProcessManagerMock->method('countBy')
      ->with(CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
        'status' => ['draft', 'new', 'rework'],
      ]))->willReturn(0);

    static::assertTrue($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['modification_date' => '2000-01-02 03:04:05']);
    $previousFundingCase = FundingCaseFactory::createFundingCase(['modification_date' => '2000-01-01 01:01:01']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_apply'],
      'type' => 'some_type',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->applicationProcessManagerMock->expects(static::never())->method('countBy');

    static::assertFalse($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
