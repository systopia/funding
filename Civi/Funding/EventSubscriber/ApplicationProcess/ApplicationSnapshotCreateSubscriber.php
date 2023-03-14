<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplicationSnapshotCreateSubscriber implements EventSubscriberInterface {

  private ApplicationProcessActionStatusInfoContainer $infoContainer;

  private ApplicationSnapshotCreateHandlerInterface $snapshotCreateHandler;

  public static function getSubscribedEvents(): array {
    // Minimal priority so every comparison is done against the state that is going to be persisted.
    return [ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', PHP_INT_MIN]];
  }

  public function __construct(
    ApplicationProcessActionStatusInfoContainer $infoContainer,
    ApplicationSnapshotCreateHandlerInterface $snapshotCreateHandler
  ) {
    $this->infoContainer = $infoContainer;
    $this->snapshotCreateHandler = $snapshotCreateHandler;
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($this->isSnapshotRequired($event)) {
      $this->snapshotCreateHandler->handle(new ApplicationSnapshotCreateCommand(
        $event->getContactId(),
        new ApplicationProcessEntityBundle(
          $event->getPreviousApplicationProcess(),
          $event->getFundingCase(),
          $event->getFundingCaseType(),
          $event->getFundingProgram(),
        ),
      ));
    }
  }

  private function getInfo(FundingCaseTypeEntity $fundingCaseType): ApplicationProcessActionStatusInfoInterface {
    return $this->infoContainer->get($fundingCaseType->getName());
  }

  private function isSnapshotRequired(ApplicationProcessPreUpdateEvent $event): bool {
    $applicationProcess = $event->getApplicationProcess();
    $previousApplicationProcess = $event->getPreviousApplicationProcess();

    return $this->getInfo($event->getFundingCaseType())->isSnapshotRequiredStatus(
      $applicationProcess->getFullStatus()
    ) && NULL === $applicationProcess->getRestoredSnapshot() && (
      $applicationProcess->getStatus() !== $previousApplicationProcess->getStatus()
      || $applicationProcess->getRequestData() != $previousApplicationProcess->getRequestData()
    );
  }

}
