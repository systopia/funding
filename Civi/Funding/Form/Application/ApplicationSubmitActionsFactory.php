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

namespace Civi\Funding\Form\Application;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;

final class ApplicationSubmitActionsFactory implements ApplicationSubmitActionsFactoryInterface {

  private FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  private FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer;

  public function __construct(
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
    FundingCaseTypeServiceLocatorContainer $serviceLocatorContainer
  ) {
    $this->metaDataProvider = $metaDataProvider;
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  /**
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>
   */
  public function getSubmitActions(ApplicationProcessEntityBundle $applicationProcessBundle, array $statusList): array {
    $actionNames = $this->getActionsDeterminer($applicationProcessBundle->getFundingCaseType())
      ->getActions($applicationProcessBundle, $statusList);

    return $this->getActions($applicationProcessBundle->getFundingCaseType(), $actionNames);
  }

  /**
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>
   */
  public function getInitialSubmitActions(
    array $permissions,
    FundingCaseTypeEntity $fundingCaseType,
    ?FundingCaseEntity $fundingCase
  ): array {
    $actionNames = $this->getActionsDeterminer($fundingCaseType)->getInitialActions($permissions, $fundingCase);

    return $this->getActions($fundingCaseType, $actionNames);
  }

  /**
   * @param list<string> $actionNames
   *
   * @return array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>
   */
  private function getActions(FundingCaseTypeEntity $fundingCaseType, array $actionNames): array {
    $metaData = $this->metaDataProvider->get($fundingCaseType->getName());

    return array_filter(
      $metaData->getApplicationProcessActions(),
      fn(string $actionName) => in_array($actionName, $actionNames, TRUE),
      ARRAY_FILTER_USE_KEY
    );
  }

  private function getActionsDeterminer(
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationProcessActionsDeterminerInterface {
    return $this->serviceLocatorContainer->get($fundingCaseType->getName())->getApplicationProcessActionsDeterminer();
  }

}
