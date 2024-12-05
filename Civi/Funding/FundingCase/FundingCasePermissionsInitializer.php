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

namespace Civi\Funding\FundingCase;

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingNewCasePermissions;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingNewCasePermissionsEntity;
use Civi\Funding\Permission\FundingCase\RelationFactory\FundingCaseContactRelationFactory;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type newCasePermissionsT array{
 *    id: int,
 *    type: string,
 *    properties: array<string, mixed>,
 *    permissions: list<string>,
 *  }
 */
class FundingCasePermissionsInitializer {

  private Api4Interface $api4;

  private FundingCaseContactRelationFactory $relationFactory;

  public function __construct(Api4Interface $api4, FundingCaseContactRelationFactory $relationFactory) {
    $this->api4 = $api4;
    $this->relationFactory = $relationFactory;
  }

  /**
   * Initializes the permissions of the given funding case based on the
   * configuration in the funding program. Normally the funding case should not
   * have any permissions set when calling this method.
   *
   * @throws \CRM_Core_Exception
   */
  public function initializePermissions(FundingCaseEntity $fundingCase): void {
    $action = FundingNewCasePermissions::get(FALSE)
      ->addWhere('funding_program_id', '=', $fundingCase->getFundingProgramId());

    /** @phpstan-var newCasePermissionsT $newCasePermissions */
    foreach ($this->api4->executeAction($action) as $newCasePermissions) {
      $createAction = FundingCaseContactRelation::create(FALSE)
        ->setValues(
          $this->relationFactory->createFundingCaseContactRelation(
            FundingNewCasePermissionsEntity::fromArray($newCasePermissions),
            $fundingCase,
          )->toArray()
        );
      $this->api4->executeAction($createAction);
    }
  }

}
