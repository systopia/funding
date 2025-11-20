<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
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

namespace Civi\Funding\Task\EventSubscriber;

use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\Funding\Task\FundingTaskManagerInterface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-type taskNameT \Civi\Funding\ActivityTypeNames::CLEARING_PROCESS_TASK
 */
class ClearingProcessTaskSubscriber implements EventSubscriberInterface {

  private FundingTaskManagerInterface $taskManager;

  /**
   * @phpstan-var array<string, iterable<\Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface>>
   */
  private array $taskCreators;

  /**
   * @phpstan-var array<string, iterable<\Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface>>
   */
  private array $taskModifiers;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ClearingProcessCreatedEvent::class => 'onCreated',
      ClearingProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  /**
   * @phpstan-param array<
   *   string,
   *   iterable<\Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface>
   * > $taskCreators
   * @phpstan-param array<
   *   string,
   *   iterable<\Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface>
   * > $taskModifiers
   */
  public function __construct(FundingTaskManagerInterface $taskManager, array $taskCreators, array $taskModifiers) {
    $this->taskManager = $taskManager;
    $this->taskCreators = $taskCreators;
    $this->taskModifiers = $taskModifiers;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ClearingProcessCreatedEvent $event): void {
    foreach ($this->taskCreators[$event->getFundingCaseType()->getName()] ?? [] as $taskCreator) {
      foreach ($taskCreator->createTasksOnNew($event->getClearingProcessBundle()) as $task) {
        $task->setValues($task->toArray() +
          ['target_contact_id' => [$event->getFundingCase()->getRecipientContactId()]]
        );
        $this->taskManager->addTask($task);
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ClearingProcessUpdatedEvent $event): void {
    $taskModifiersByActivityTypeName = $this->getTaskModifiersByActivityTypeName(
      $event->getFundingCaseType()->getName()
    );
    foreach ($taskModifiersByActivityTypeName as $activityTypeName => $taskModifiers) {
      $openTasks = $this->taskManager->getOpenTasksBy($activityTypeName, Comparison::new(
        'funding_clearing_process_task.clearing_process_id',
        '=',
        $event->getClearingProcess()->getId()
      ));

      foreach ($openTasks as $task) {
        $modified = FALSE;
        foreach ($taskModifiers as $taskModifier) {
          if ($taskModifier->modifyTask(
            $task,
            $event->getClearingProcessBundle(),
            $event->getPreviousClearingProcess()
          )) {
            $modified = TRUE;
          }
        }
        if ($modified) {
          $this->taskManager->updateTask($task);
        }
      }
    }

    foreach ($this->taskCreators[$event->getFundingCaseType()->getName()] ?? [] as $taskCreator) {
      $tasks = $taskCreator->createTasksOnChange(
        $event->getClearingProcessBundle(),
        $event->getPreviousClearingProcess()
      );
      foreach ($tasks as $task) {
        $task->setValues($task->toArray() +
          ['target_contact_id' => [$event->getFundingCase()->getRecipientContactId()]]
        );
        $this->taskManager->addTask($task);
      }
    }
  }

  /**
   * @phpstan-return array<
   *   taskNameT, non-empty-list<\Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface>
   * >
   */
  private function getTaskModifiersByActivityTypeName(string $fundingCaseTypeName): array {
    $taskModifiers = [];
    foreach ($this->taskModifiers[$fundingCaseTypeName] ?? [] as $taskModifier) {
      $taskModifiers[$taskModifier->getActivityTypeName()][] = $taskModifier;
    }

    return $taskModifiers;
  }

}
