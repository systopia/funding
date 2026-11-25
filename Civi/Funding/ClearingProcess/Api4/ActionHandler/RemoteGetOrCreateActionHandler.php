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
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

/**
 * @phpstan-import-type clearingProcessT from \Civi\Funding\Entity\ClearingProcessEntity
 */
final class RemoteGetOrCreateActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingClearingProcess';

  public function __construct(
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly ClearingProcessManager $clearingProcessManager,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider
  ) {}

  /**
   * @phpstan-return clearingProcessT
   *
   * @throws \CRM_Core_Exception
   */
  public function getOrCreate(GetOrCreateAction $action): array {
    $applicationProcessBundle = $this->applicationProcessManager->getBundle($action->getApplicationProcessId());
    if (NULL === $applicationProcessBundle) {
      throw new \CRM_Core_Exception(
        sprintf('No application process with ID %d found', $action->getApplicationProcessId())
      );
    }

    if (!$this->isClearingAvailable($applicationProcessBundle)) {
      throw new \CRM_Core_Exception(
        sprintf('Application process with ID %d is currently not clearable', $action->getApplicationProcessId())
      );
    }

    $clearingProcess = $this->clearingProcessManager->getByApplicationProcessId($action->getApplicationProcessId());

    if (NULL === $clearingProcess) {
      throw new UnauthorizedException(sprintf(
        'Permission to access clearing for application process with ID %d is missing',
        $action->getApplicationProcessId()
      ));
    }

    if (NULL === $clearingProcess->getCreationDate()) {
      $fundingCase = $applicationProcessBundle->getFundingCase();
      if (!$fundingCase->hasPermission(ClearingProcessPermissions::CLEARING_APPLY)
        && !$fundingCase->hasPermission(ClearingProcessPermissions::CLEARING_MODIFY)
      ) {
        throw new UnauthorizedException(sprintf(
        'Permission to start clearing for application process with ID %d is missing',
        $action->getApplicationProcessId()
        ));
      }

      $this->clearingProcessManager->start(
        new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle)
      );
    }

    return $clearingProcess->toArray();
  }

  private function isClearingAvailable(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    $metaData = $this->metaDataProvider->get($applicationProcessBundle->getFundingCaseType()->getName());
    $status = $metaData->getApplicationProcessStatus($applicationProcessBundle->getApplicationProcess()->getStatus());

    return (bool) $status?->isClearingAvailable();
  }

}
