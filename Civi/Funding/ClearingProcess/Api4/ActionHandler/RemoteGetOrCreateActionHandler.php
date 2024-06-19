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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetOrCreateAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

/**
 * @phpstan-import-type clearingProcessT from \Civi\Funding\Entity\ClearingProcessEntity
 */
final class RemoteGetOrCreateActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingClearingProcess';

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ClearingProcessManager $clearingProcessManager;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ClearingProcessManager $clearingProcessManager
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->clearingProcessManager = $clearingProcessManager;
  }

  /**
   * @phpstan-return clearingProcessT
   *
   * @throws \CRM_Core_Exception
   */
  public function getOrCreate(GetOrCreateAction $action): array {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($action->getApplicationProcessId());
    if (NULL === $applicationProcessBundle) {
      throw new \CRM_Core_Exception(
        sprintf('No application process with ID %d found', $action->getApplicationProcessId())
      );
    }

    if (TRUE !== $applicationProcessBundle->getApplicationProcess()->getIsEligible()) {
      throw new \CRM_Core_Exception(
        sprintf('Application process with ID %d is not in an eligible status', $action->getApplicationProcessId())
      );
    }

    $clearingProcess = $this->clearingProcessManager->getByApplicationProcessId($action->getApplicationProcessId());

    if (NULL === $clearingProcess) {
      $fundingCase = $applicationProcessBundle->getFundingCase();
      if (!$fundingCase->hasPermission(ClearingProcessPermissions::CLEARING_APPLY)
        && !$fundingCase->hasPermission(ClearingProcessPermissions::CLEARING_MODIFY)
      ) {
        throw new UnauthorizedException(sprintf(
          'Permission to create clearing for application process with ID %d is missing',
          $action->getApplicationProcessId()
        ));
      }

      $clearingProcess = $this->clearingProcessManager->create($applicationProcessBundle);
    }

    return $clearingProcess->toArray();
  }

}
