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

namespace Civi\Funding\Permission\FundingCase;

use Civi\Api4\FundingCaseContactRelation;
use Civi\Funding\Entity\FundingCaseContactRelationEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\RemoteTools\Api4\Api4Interface;

final class FundingCaseContactsLoader implements FundingCaseContactsLoaderInterface {

  private Api4Interface $api4;

  private ContactsWithPermissionLoader $contactsLoader;

  public function __construct(Api4Interface $api4, ContactsWithPermissionLoader $contactsLoader) {
    $this->api4 = $api4;
    $this->contactsLoader = $contactsLoader;
  }

  public function getContactsWithPermission(FundingCaseEntity $fundingCase, string $permission): array {
    $action = $this->api4->createGetAction(FundingCaseContactRelation::getEntityName())
      ->setCheckPermissions(FALSE)
      ->addWhere('funding_case_id', '=', $fundingCase->getId());
    $result = $this->api4->executeAction($action);
    $contactRelations = FundingCaseContactRelationEntity::allFromApiResult($result);

    return $this->contactsLoader->getContactsWithPermission($contactRelations, $permission);
  }

}
