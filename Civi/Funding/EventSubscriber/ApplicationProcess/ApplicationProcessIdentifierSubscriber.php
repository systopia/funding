<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingTask;
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessIdentifierSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private ApplicationIdentifierGeneratorInterface $applicationIdentifierGenerator;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessCreatedEvent::class => ['onCreated', PHP_INT_MAX],
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
    ];
  }

  public function __construct(
    Api4Interface $api4,
    ApplicationIdentifierGeneratorInterface $applicationIdentifierGenerator
  ) {
    $this->api4 = $api4;
    $this->applicationIdentifierGenerator = $applicationIdentifierGenerator;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $identifier = $this->applicationIdentifierGenerator->generateIdentifier($event->getApplicationProcessBundle());
    $applicationProcess = $event->getApplicationProcess();
    $applicationProcess->setIdentifier($identifier);
    $action = (new DAOUpdateAction(FundingApplicationProcess::getEntityName(), 'update'))
      ->setCheckPermissions(FALSE)
      ->addValue('identifier', $identifier)
      ->addWhere('id', '=', $applicationProcess->getId());
    $this->api4->executeAction($action);
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($event->getFundingCase()->getId() !== $event->getPreviousFundingCase()->getId()) {
      $applicationProcessBundle = $event->getApplicationProcessBundle();
      $applicationProcess = $applicationProcessBundle->getApplicationProcess();
      $newIdentifier = $this->applicationIdentifierGenerator
        ->generateIdentifierOnFundingCaseChange($applicationProcessBundle);
      if ($newIdentifier !== $applicationProcess->getIdentifier()) {
        $taskUpdate = FundingTask::update(FALSE)
          ->setIgnoreCasePermissions(TRUE)
          ->addValue('funding_case_task.affected_identifier', $newIdentifier)
          ->addWhere('funding_case_task.affected_identifier', '=', $applicationProcess->getIdentifier());
        $this->api4->executeAction($taskUpdate);

        $applicationProcess->setIdentifier($newIdentifier);
      }
    }
  }

}
