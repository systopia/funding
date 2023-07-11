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

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Webmozart\Assert\Assert;

final class ApplicationSnapshotRestorer implements ApplicationSnapshotRestorerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationSnapshotManager $applicationSnapshotManager;

  private ApplicationExternalFileManagerInterface $externalFileManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ApplicationSnapshotManager $applicationSnapshotManager,
    ApplicationExternalFileManagerInterface $externalFileManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->applicationSnapshotManager = $applicationSnapshotManager;
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function restoreLastSnapshot(int $contactId, ApplicationProcessEntityBundle $applicationProcessBundle): void {
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
    $applicationProcess->setIsReviewCalculative($applicationSnapshot->getIsReviewCalculative());
    $applicationProcess->setIsReviewContent($applicationSnapshot->getIsReviewContent());
    $applicationProcess->setIsEligible($applicationSnapshot->getIsEligible());
    $applicationProcess->setRestoredSnapshot($applicationSnapshot);

    $this->applicationProcessManager->update($contactId, $applicationProcessBundle);

    $usedIdentifiers = [];
    $externalFiles = $this->externalFileManager->getFilesForSnapshot($applicationSnapshot->getId());
    foreach ($externalFiles as $externalFile) {
      $this->externalFileManager->restoreSnapshot($externalFile, $applicationProcess->getId());
      $usedIdentifiers[] = $externalFile->getIdentifier();
    }

    $this->externalFileManager->deleteFiles($applicationProcess->getId(), $usedIdentifiers);
  }

}
