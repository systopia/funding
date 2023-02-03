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

use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;

class ContactsWithPermissionLoader {

  private ContactRelationLoaderInterface $contactRelationLoader;

  public function __construct(ContactRelationLoaderInterface $contactRelationLoader) {
    $this->contactRelationLoader = $contactRelationLoader;
  }

  /**
   * @phpstan-param array<\Civi\Funding\Entity\FundingCaseContactRelationEntity> $contactRelations
   *
   * @phpstan-return array<int, array<string, mixed>>
   *   Contacts indexed by id.
   */
  public function getContactsWithPermission(array $contactRelations, string $permission): array {
    $contacts = [];
    foreach ($contactRelations as $contactRelation) {
      if (in_array($permission, $contactRelation->getPermissions(), TRUE)) {
        $contacts += $this->contactRelationLoader->getContacts(
          $contactRelation->getType(),
          $contactRelation->getProperties()
        );
      }
    }

    return $contacts;
  }

}
