<?php
declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingCreateTaskModifier
 */
final class AbstractClearingCreateTaskModifierTest extends TestCase {

  private AbstractClearingCreateTaskModifier $taskModifier;

  protected function setUp(): void {
    parent::setUp();
    $this->taskModifier = $this->getMockForAbstractClass(AbstractClearingCreateTaskModifier::class);
  }

  public function testModifyTaskNotStarted(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'not-started']);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'not-started']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Create clearing',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::CLEARING_APPLY,
        ClearingProcessPermissions::CLEARING_MODIFY,
      ],
      'type' => 'create',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
    ]);

    static::assertFalse($this->taskModifier->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskStarted(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'draft']);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'not-started']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Create clearing',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::CLEARING_APPLY,
        ClearingProcessPermissions::CLEARING_MODIFY,
      ],
      'type' => 'create',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
    ]);

    static::assertTrue($this->taskModifier->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create(['status' => 'not-started']);
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'not-started']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Create clearing',
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::CLEARING_APPLY,
        ClearingProcessPermissions::CLEARING_MODIFY,
      ],
      'type' => 'createX',
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
    ]);

    static::assertFalse($this->taskModifier->modifyTask($task, $clearingProcessBundle, $previousClearingProcess));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
