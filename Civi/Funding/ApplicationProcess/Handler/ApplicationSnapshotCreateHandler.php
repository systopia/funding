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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationSnapshotCreateCommand;
use Civi\Funding\Entity\ApplicationSnapshotEntity;
use Civi\Funding\Util\DateTimeUtil;

final class ApplicationSnapshotCreateHandler implements ApplicationSnapshotCreateHandlerInterface {

  private ApplicationSnapshotManager $applicationSnapshotManager;

  private ApplicationExternalFileManagerInterface $externalFileManager;

  public function __construct(
    ApplicationSnapshotManager $applicationSnapshotManager,
    ApplicationExternalFileManagerInterface $externalFileManager
  ) {
    $this->applicationSnapshotManager = $applicationSnapshotManager;
    $this->externalFileManager = $externalFileManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationSnapshotCreateCommand $command): void {
    $applicationProcess = $command->getApplicationProcess();
    $applicationSnapshot = ApplicationSnapshotEntity::fromArray([
      'application_process_id' => $applicationProcess->getId(),
      'status' => $applicationProcess->getStatus(),
      'creation_date' => date('Y-m-d H:i:s'),
      'title' => $applicationProcess->getTitle(),
      'short_description' => $applicationProcess->getShortDescription(),
      'start_date' => DateTimeUtil::toDateTimeStrOrNull($applicationProcess->getStartDate()),
      'end_date' => DateTimeUtil::toDateTimeStrOrNull($applicationProcess->getEndDate()),
      'request_data' => $applicationProcess->getRequestData(),
      'amount_requested' => $applicationProcess->getAmountRequested(),
      'is_review_content' => $applicationProcess->getIsReviewContent(),
      'is_review_calculative' => $applicationProcess->getIsReviewCalculative(),
      'is_eligible' => $applicationProcess->getIsEligible(),
    ]);

    $this->applicationSnapshotManager->add($applicationSnapshot);

    $externalFiles = $this->externalFileManager->getFiles($applicationProcess->getId());
    foreach ($externalFiles as $externalFile) {
      $this->externalFileManager->attachFileToSnapshot($externalFile, $applicationSnapshot->getId());
    }
  }

}
