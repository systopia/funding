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

namespace Civi\Funding\Fixtures\Traits;

use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Fixtures\ApplicationCostItemFixture;
use Civi\Funding\Fixtures\ApplicationResourcesItemFixture;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\EntityFileFixture;
use Civi\Funding\Fixtures\ExternalFileFixture;

trait ClearingProcessFixturesTrait {

  protected ClearingProcessEntityBundle $clearingProcessBundle;

  protected ApplicationCostItemEntity $costItem;

  protected ExternalFileEntity $externalFile;

  protected ApplicationResourcesItemEntity $resourcesItem;

  /**
   * @param array<string, mixed> $clearingProcessValues
   *
   * @throws \CRM_Core_Exception
   */
  protected function addFixtures(array $clearingProcessValues = []): void {
    $this->clearingProcessBundle = ClearingProcessBundleFixture::create(
      $clearingProcessValues, [
        'start_date' => '2024-03-04',
        'end_date' => '2024-03-05',
        'request_data' => ['amountRequested' => 10, 'resources' => 20],
      ]
    );
    $applicationProcessId = $this->clearingProcessBundle->getApplicationProcess()->getId();
    $this->costItem = ApplicationCostItemFixture::addFixture($applicationProcessId, [
      'identifier' => 'amountRequested',
    ]);
    $this->resourcesItem = ApplicationResourcesItemFixture::addFixture($applicationProcessId, [
      'identifier' => 'resources',
    ]);

    $this->externalFile = ExternalFileFixture::addFixture([
      'identifier' => 'FundingApplicationProcess.' . $applicationProcessId . ':file',
    ]);
    EntityFileFixture::addFixture(
      'civicrm_funding_application_process',
      $applicationProcessId,
      $this->externalFile->getFileId(),
    );
  }

}
