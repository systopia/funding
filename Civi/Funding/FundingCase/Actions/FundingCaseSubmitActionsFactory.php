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

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;

final class FundingCaseSubmitActionsFactory implements FundingCaseSubmitActionsFactoryInterface {

  private FundingCaseActionsDeterminerInterface $actionDeterminer;

  private FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  public function __construct(
    FundingCaseActionsDeterminerInterface $actionDeterminer,
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
  ) {
    $this->actionDeterminer = $actionDeterminer;
    $this->metaDataProvider = $metaDataProvider;
  }

  public function getSubmitActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array {
    $actionNames = $this->actionDeterminer->getActions($fundingCaseBundle, $applicationProcessStatusList);

    return $this->getActions($fundingCaseBundle->getFundingCaseType(), $actionNames);
  }

  public function getInitialSubmitActions(array $permissions, FundingCaseTypeEntity $fundingCaseType): array {
    $actionNames = $this->actionDeterminer->getInitialActions($fundingCaseType, $permissions);

    return $this->getActions($fundingCaseType, $actionNames);
  }

  /**
   * @param list<string> $actionNames
   *
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\FundingCaseAction>
   */
  private function getActions(FundingCaseTypeEntity $fundingCaseType, array $actionNames): array {
    $metaData = $this->metaDataProvider->get($fundingCaseType->getName());

    return array_filter(
      $metaData->getFundingCaseActions(),
      fn(string $actionName) => in_array($actionName, $actionNames, TRUE),
      ARRAY_FILTER_USE_KEY
    );
  }

}
