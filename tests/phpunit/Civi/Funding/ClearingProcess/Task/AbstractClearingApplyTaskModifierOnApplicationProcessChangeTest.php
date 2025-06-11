<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Task\AbstractClearingApplyTaskModifierOnApplicationProcessChange
 */
final class AbstractClearingApplyTaskModifierOnApplicationProcessChangeTest extends TestCase {

  protected static ?\DateTime $dueDate = NULL;

  private AbstractClearingApplyTaskModifierOnApplicationProcessChange $taskModifier;

  protected function setUp(): void {
    parent::setUp();
    $this->taskModifier = new class() extends AbstractClearingApplyTaskModifierOnApplicationProcessChange {

      public static ?\DateTimeInterface $dueDate = NULL;

      /**
       * @inheritDoc
       */
      public static function getSupportedFundingCaseTypes(): array {
        return [];
      }

      protected function getDueDate(
        ApplicationProcessEntityBundle $applicationProcessBundle,
        ApplicationProcessEntity $previousApplicationProcess,
        FundingTaskEntity $task
      ): ?\DateTimeInterface {
        return self::$dueDate;
      }

    };
  }

  public function testGetActivityTypeName(): void {
    static::assertSame(ActivityTypeNames::CLEARING_PROCESS_TASK, $this->taskModifier->getActivityTypeName());
  }

  public function testModifyTask(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();

    $task = FundingTaskEntity::newTask([
      'subject' => 'Complete and Apply Clearing',
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [ClearingProcessPermissions::CLEARING_APPLY],
      'type' => 'apply',
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => 123,
      'due_date' => new \DateTime('2000-01-02'),
    ]);

    // @phpstan-ignore staticProperty.notFound
    $this->taskModifier::$dueDate = new \DateTime('2000-01-03');
    static::assertTrue($this->taskModifier->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertEquals(new \DateTime('2000-01-03'), $task->getDueDate());

    static::assertFalse($this->taskModifier->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
  }

  public function testModifyTaskDifferentTaskType(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess();

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Task',
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => [
        ClearingProcessPermissions::CLEARING_APPLY,
        ClearingProcessPermissions::CLEARING_MODIFY,
      ],
      'type' => 'some_type',
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => 123,
      'due_date' => new \DateTime('2000-01-02'),
    ]);

    static::assertFalse($this->taskModifier->modifyTask($task, $applicationProcessBundle, $previousApplicationProcess));
    static::assertEquals(new \DateTime('2000-01-02'), $task->getDueDate());
  }

}
