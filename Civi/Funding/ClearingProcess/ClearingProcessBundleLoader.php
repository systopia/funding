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

namespace Civi\Funding\ClearingProcess;

use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Webmozart\Assert\Assert;

class ClearingProcessBundleLoader {

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
   * @throws \CRM_Core_Exception
   */
  public function get(int $clearingProcessId): ?ClearingProcessEntityBundle {
    $clearingProcess = $this->clearingProcessManager->get($clearingProcessId);

    return NULL === $clearingProcess ? NULL : $this->createFromClearingProcess($clearingProcess);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createFromClearingProcess(
    ClearingProcessEntity $clearingProcess
  ): ClearingProcessEntityBundle {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($clearingProcess->getApplicationProcessId());
    Assert::notNull($applicationProcessBundle);

    return new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle);
  }

}
