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

namespace Civi\Funding\ApplicationProcess\Snapshot;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\Entity\ApplicationSnapshotEntity;
use Webmozart\Assert\Assert;

final class ApplicationSnapshotRestorer implements ApplicationSnapshotRestorerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationSnapshotManager $applicationSnapshotManager;

  private ApplicationCostItemManager $costItemManager;

  private ApplicationExternalFileManagerInterface $externalFileManager;

  private ApplicationResourcesItemManager $resourcesItemManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationSnapshotManager $applicationSnapshotManager,
    ApplicationCostItemManager $costItemManager,
    ApplicationExternalFileManagerInterface $externalFileManager,
    ApplicationResourcesItemManager $resourcesItemManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->applicationSnapshotManager = $applicationSnapshotManager;
    $this->costItemManager = $costItemManager;
    $this->externalFileManager = $externalFileManager;
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function restoreLastSnapshot(ApplicationProcessEntityBundle $applicationProcessBundle): void {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();

    $applicationSnapshot = $this->applicationSnapshotManager->getLastByApplicationProcessId(
      $applicationProcess->getId()
    );
    Assert::notNull($applicationSnapshot, 'Application snapshot to restore not found');

    $applicationProcess->setStatus($applicationSnapshot->getStatus());
    $applicationProcess->setTitle($applicationSnapshot->getTitle());
    $applicationProcess->setShortDescription($applicationSnapshot->getShortDescription());
    $applicationProcess->setStartDate($applicationSnapshot->getStartDate());
    $applicationProcess->setEndDate($applicationSnapshot->getEndDate());
    $applicationProcess->setRequestData($applicationSnapshot->getRequestData());
    $applicationProcess->setAmountRequested($applicationSnapshot->getAmountRequested());
    $applicationProcess->setAmountEligible($applicationSnapshot->getAmountEligible());
    $applicationProcess->setIsReviewCalculative($applicationSnapshot->getIsReviewCalculative());
    $applicationProcess->setIsReviewContent($applicationSnapshot->getIsReviewContent());
    $applicationProcess->setIsEligible($applicationSnapshot->getIsEligible());
    $applicationProcess->setIsInWork($applicationSnapshot->getIsInWork());
    $applicationProcess->setIsRejected($applicationSnapshot->getIsRejected());
    $applicationProcess->setIsWithdrawn($applicationSnapshot->getIsWithdrawn());
    $applicationProcess->setRestoredSnapshot($applicationSnapshot);
    // @phpstan-ignore-next-line
    $applicationProcess->setValues($applicationProcess->toArray() + $applicationSnapshot->getCustomFields());

    $this->applicationProcessManager->update($applicationProcessBundle);

    $this->restoreCostItems($applicationSnapshot);
    $this->restoreResourcesItems($applicationSnapshot);

    $usedIdentifiers = [];
    $externalFiles = $this->externalFileManager->getFilesAttachedToSnapshot($applicationSnapshot->getId());
    foreach ($externalFiles as $externalFile) {
      $this->externalFileManager->restoreFileSnapshot($externalFile, $applicationProcess->getId());
      $usedIdentifiers[] = $externalFile->getIdentifier();
    }

    $this->externalFileManager->deleteFiles($applicationProcess->getId(), $usedIdentifiers);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function restoreCostItems(ApplicationSnapshotEntity $applicationSnapshot): void {
    $costItems = [];
    foreach ($applicationSnapshot->getCostItems() as $costItemData) {
      $costItems[] = ApplicationCostItemEntity::fromArray($costItemData);
    }

    $this->costItemManager->updateAll($applicationSnapshot->getApplicationProcessId(), $costItems);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function restoreResourcesItems(ApplicationSnapshotEntity $applicationSnapshot): void {
    $resourcesItems = [];
    foreach ($applicationSnapshot->getResourcesItems() as $resourcesItemData) {
      $resourcesItems[] = ApplicationResourcesItemEntity::fromArray($resourcesItemData);
    }

    $this->resourcesItemManager->updateAll($applicationSnapshot->getApplicationProcessId(), $resourcesItems);
  }

}
