<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\AbstractBatchAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

final class DeleteAction extends AbstractBatchAction {

  private Api4Interface $api4;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  public function __construct(
    Api4Interface $api4,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationProcessManager $applicationProcessManager,
    ApplicationProcessActionsDeterminerInterface $actionsDeterminer
  ) {
    parent::__construct(FundingApplicationProcess::_getEntityName(), 'delete');
    $this->api4 = $api4;
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->actionsDeterminer = $actionsDeterminer;
  }

  /**
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $applicationProcessBundles = $this->getApplicationProcessBundles();
    foreach ($applicationProcessBundles as $applicationProcessBundle) {
      if (!$this->isDeleteAllowed($applicationProcessBundle)) {
        throw new UnauthorizedException('Deletion is not allowed');
      }
    }

    foreach ($applicationProcessBundles as $applicationProcessBundle) {
      $this->applicationProcessManager->delete($applicationProcessBundle);
      $result[] = ['id' => $applicationProcessBundle->getApplicationProcess()->getId()];
    }
  }

  /**
   * @phpstan-return array<\Civi\Funding\Entity\ApplicationProcessEntityBundle>
   * @throws \API_Exception
   */
  private function getApplicationProcessBundles(): array {
    $action = FundingApplicationProcess::get()
      ->addSelect('id')
      ->setWhere($this->getWhere());

    /** @var array<array{id: int}> $records */
    $records = $this->api4->executeAction($action)->getArrayCopy();

    return \array_map(
      function (array $values): ApplicationProcessEntityBundle {
        $applicationProcessBundle = $this->applicationProcessBundleLoader->get($values['id']);
        Assert::notNull($applicationProcessBundle);

        return $applicationProcessBundle;
      }, $records,
    );
  }

  private function isDeleteAllowed(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return $this->actionsDeterminer->isActionAllowed(
      'delete',
      $applicationProcessBundle->getApplicationProcess()->getFullStatus(),
      $applicationProcessBundle->getFundingCase()->getPermissions(),
    );
  }

}
