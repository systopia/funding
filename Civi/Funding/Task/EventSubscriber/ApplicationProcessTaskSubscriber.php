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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Task\FundingTaskManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessTaskSubscriber implements EventSubscriberInterface {

  private FundingTaskManager $taskManager;

  /**
   * @phpstan-var array<string, iterable<\Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface>>
   */
  private array $taskCreators;

  /**
   * @phpstan-var array<string, iterable<\Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface>>
   */
  private array $taskModifiers;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  /**
   * @phpstan-param array<
   *   string,
   *   iterable<\Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface>
   * > $taskCreators
   * @phpstan-param array<
   *   string,
   *   iterable<\Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface>
   * > $taskModifiers
   */
  public function __construct(FundingTaskManager $taskManager, array $taskCreators, array $taskModifiers) {
    $this->taskManager = $taskManager;
    $this->taskCreators = $taskCreators;
    $this->taskModifiers = $taskModifiers;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    foreach ($this->taskCreators[$event->getFundingCaseType()->getName()] ?? [] as $taskCreator) {
      foreach ($taskCreator->createTasksOnNew($event->getApplicationProcessBundle()) as $task) {
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
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    $openTasks = $this->taskManager->getOpenTasks(
      ActivityTypeNames::APPLICATION_PROCESS_TASK,
      $event->getApplicationProcess()->getId()
    );
    foreach ($openTasks as $task) {
      $modified = FALSE;
      foreach ($this->taskModifiers[$event->getFundingCaseType()->getName()] ?? [] as $taskModifier) {
        if ($taskModifier->modifyTask(
          $task,
          $event->getApplicationProcessBundle(),
          $event->getPreviousApplicationProcess()
        )) {
          $modified = TRUE;
        }
      }
      if ($modified) {
        $this->taskManager->updateTask($task);
      }
    }

    foreach ($this->taskCreators[$event->getFundingCaseType()->getName()] ?? [] as $taskCreator) {
      $tasks = $taskCreator->createTasksOnChange(
        $event->getApplicationProcessBundle(),
        $event->getPreviousApplicationProcess()
      );
      foreach ($tasks as $task) {
        $task->setValues($task->toArray() +
          ['target_contact_id' => [$event->getFundingCase()->getRecipientContactId()]]
        );
        $this->taskManager->addTask($task);
      }
    }
  }

}
