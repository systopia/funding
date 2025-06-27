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

use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationSnapshotCreateSubscriber implements EventSubscriberInterface {

  private FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  private ApplicationSnapshotCreateHandlerInterface $snapshotCreateHandler;

  public static function getSubscribedEvents(): array {
    // Minimal priority so every comparison is done against the state that is going to be persisted.
    return [ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', PHP_INT_MIN]];
  }

  public function __construct(
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
    ApplicationSnapshotCreateHandlerInterface $snapshotCreateHandler
  ) {
    $this->metaDataProvider = $metaDataProvider;
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

  private function getMetaData(FundingCaseTypeEntity $fundingCaseType): FundingCaseTypeMetaDataInterface {
    return $this->metaDataProvider->get($fundingCaseType->getName());
  }

  private function isSnapshotRequired(ApplicationProcessPreUpdateEvent $event): bool {
    $applicationProcess = $event->getApplicationProcess();
    $previousApplicationProcess = $event->getPreviousApplicationProcess();
    $status = $this->getMetaData($event->getFundingCaseType())
      ->getApplicationProcessStatus($applicationProcess->getStatus());

    return ($status?->isSnapshotRequired() ?? TRUE) && NULL === $applicationProcess->getRestoredSnapshot() && (
      $applicationProcess->getStatus() !== $previousApplicationProcess->getStatus()
      || $applicationProcess->getRequestData() != $previousApplicationProcess->getRequestData()
    );
  }

}
