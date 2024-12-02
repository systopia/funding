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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Webmozart\Assert\Assert;

class ApplicationProcessBundleLoader {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  public function countBy(ConditionInterface $condition): int {
    return $this->applicationProcessManager->countBy($condition);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $applicationProcessId): ?ApplicationProcessEntityBundle {
    $applicationProcess = $this->applicationProcessManager->get($applicationProcessId);

    return NULL === $applicationProcess ? NULL : $this->createFromApplicationProcess($applicationProcess);
  }

  /**
   * @phpstan-return list<ApplicationProcessEntityBundle>
   *
   * @throws \CRM_Core_Exception
   */
  public function getAll(): array {
    $bundles = [];
    foreach ($this->applicationProcessManager->getAll() as $applicationProcess) {
      $bundles[] = $this->createFromApplicationProcess($applicationProcess);
    }

    return $bundles;
  }

  /**
   * @phpstan-param array<string, 'ASC'|'DESC'> $orderBy
   *
   * @phpstan-return list<ApplicationProcessEntityBundle>
   *
   * @throws \CRM_Core_Exception
   */
  public function getBy(
    ConditionInterface $condition,
    array $orderBy = [],
    int $limit = 0,
    int $offset = 0
  ): array {
    $bundles = [];
    foreach ($this->applicationProcessManager->getBy($condition, $orderBy, $limit, $offset) as $applicationProcess) {
      $bundles[] = $this->createFromApplicationProcess($applicationProcess);
    }

    return $bundles;
  }

  /**
   * @phpstan-return array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   *   Status of other application processes in same funding case indexed by ID.
   *
   * @throws \CRM_Core_Exception
   */
  public function getStatusList(ApplicationProcessEntityBundle $applicationProcessBundle): array {
    $statusList = $this->applicationProcessManager->getStatusListByFundingCaseId(
      $applicationProcessBundle->getFundingCase()->getId()
    );
    unset($statusList[$applicationProcessBundle->getApplicationProcess()->getId()]);

    return $statusList;
  }

  /**
   * @phpstan-return array<ApplicationProcessEntityBundle>
   */
  public function getByFundingCaseId(int $fundingCaseId): array {
    return array_map(
      [$this, 'createFromApplicationProcess'],
      $this->applicationProcessManager->getByFundingCaseId($fundingCaseId),
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFirstByFundingCaseId(int $fundingCaseId): ?ApplicationProcessEntityBundle {
    $applicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCaseId);

    return NULL === $applicationProcess ? NULL : $this->createFromApplicationProcess($applicationProcess);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createFromApplicationProcess(
    ApplicationProcessEntity $applicationProcess
  ): ApplicationProcessEntityBundle {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($applicationProcess->getFundingCaseId());
    Assert::notNull($fundingCaseBundle);

    return new ApplicationProcessEntityBundle(
      $applicationProcess,
      $fundingCaseBundle->getFundingCase(),
      $fundingCaseBundle->getFundingCaseType(),
      $fundingCaseBundle->getFundingProgram()
    );
  }

}
