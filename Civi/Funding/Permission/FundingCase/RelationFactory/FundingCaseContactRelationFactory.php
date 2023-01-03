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

namespace Civi\Funding\Permission\FundingCase\RelationFactory;

use Civi\Funding\Entity\FundingCaseContactRelationEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingNewCasePermissionsEntity;

final class FundingCaseContactRelationFactory {

  private RelationPropertiesFactoryLocator $propertiesFactoryLocator;

  public function __construct(RelationPropertiesFactoryLocator $propertiesFactoryLocator) {
    $this->propertiesFactoryLocator = $propertiesFactoryLocator;
  }

  public function createFundingCaseContactRelation(
    FundingNewCasePermissionsEntity $newCasePermissions,
    FundingCaseEntity $fundingCase
  ): FundingCaseContactRelationEntity {
    $propertiesFactory = $this->propertiesFactoryLocator->get($newCasePermissions->getType());

    return FundingCaseContactRelationEntity::fromArray([
      'funding_case_id' => $fundingCase->getId(),
      'type' => $propertiesFactory->getRelationType(),
      'properties' => $propertiesFactory->createRelationProperties(
        $newCasePermissions->getProperties(),
        $fundingCase,
      ),
      'permissions' => $newCasePermissions->getPermissions(),
    ]);
  }

}
