<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Task;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Task\AbstractFundingCaseApproveTaskModifier
 */
final class AbstractFundingCaseApproveTaskModifierTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private AbstractFundingCaseApproveTaskModifier $taskModifier;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->taskModifier = $this->getMockForAbstractClass(
      AbstractFundingCaseApproveTaskModifier::class,
      [$this->api4Mock]
    );
  }

  public function testModifyTaskApproved(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => 'ongoing',
      'amount_approved' => 123,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'open',
      'amount_approved' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Approve Funding Case',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_review'],
      'type' => 'approve',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    static::assertTrue($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskNotApproved(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => 'open',
      'amount_approved' => NULL,
      'modification_date' => '2000-01-02 03:04:05',
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'open',
      'amount_approved' => NULL,
      'modification_date' => '2000-01-01 01:01:01',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Approve Funding Case',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_review'],
      'type' => 'approve',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->api4Mock->expects(static::once())->method('countEntities')
      ->with(FundingApplicationProcess::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
        'is_eligible' => NULL,
      ]))->willReturn(0);

    static::assertFalse($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskNotApprovedWithUndecidedApplication(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => 'open',
      'amount_approved' => NULL,
      'modification_date' => '2000-01-02 03:04:05',
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'open',
      'amount_approved' => NULL,
      'modification_date' => '2000-01-01 01:01:01',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Approve Funding Case',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_review'],
      'type' => 'approve',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->api4Mock->expects(static::once())->method('countEntities')
      ->with(FundingApplicationProcess::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
        'is_eligible' => NULL,
      ]))->willReturn(1);

    static::assertTrue($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => 'ongoing',
      'amount_approved' => 123,
    ]);
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'open',
      'amount_approved' => NULL,
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Approve Funding Case',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['application_review'],
      'type' => 'some_type',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    static::assertFalse($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
