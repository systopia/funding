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

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationSnapshotCreatedEvent;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationSnapshotCreateSubscriber implements EventSubscriberInterface {

  private FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  private ApplicationSnapshotCreateHandlerInterface $snapshotCreateHandler;

  private ApplicationProcessActivityManager $activityManager;

  private RequestContextInterface $requestContext;

  public static function getSubscribedEvents(): array {
    // Minimal priority so every comparison is done against the state that is going to be persisted.
    return [
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', PHP_INT_MIN],
      ApplicationSnapshotCreatedEvent::class => ['onSnapshotCreated'],
    ];
  }

  public function __construct(
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
    ApplicationSnapshotCreateHandlerInterface $snapshotCreateHandler,
    ApplicationProcessActivityManager $activityManager,
    RequestContextInterface $requestContext
  ) {
    $this->metaDataProvider = $metaDataProvider;
    $this->snapshotCreateHandler = $snapshotCreateHandler;
    $this->activityManager = $activityManager;
    $this->requestContext = $requestContext;
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($this->isSnapshotRequired($event)) {
      $this->snapshotCreateHandler->handle(new ApplicationSnapshotCreateCommand(
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
        // @phpstan-ignore notEqual.notAllowed
        || $applicationProcess->getRequestData() != $previousApplicationProcess->getRequestData()
      );
  }

  public function onSnapshotCreated(ApplicationSnapshotCreatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $applicationSnapshot = $event->getApplicationSnapshot();

    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_APPLICATION_SNAPSHOT_CREATION,
      'subject' => E::ts('Funding Application Snapshot Created'),
      'details' => E::ts('Application: %1 (%2)', [
        1 => $applicationProcess->getTitle(),
        2 => $applicationProcess->getIdentifier(),
      ]),
      'funding_application_snapshot_creation.snapshot_id' => $applicationSnapshot->getId(),
    ]);

    $this->activityManager->addActivity(
      $this->requestContext->getContactId(),
      $applicationProcess,
      $activity
    );
  }

}
