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

use Civi\Funding\Entity\FundingCaseEntity;

final class FundingCaseContactsLoaderCollection implements FundingCaseContactsLoaderInterface {

  /**
   * @phpstan-var iterable<FundingCaseContactsLoaderInterface>
   */
  private iterable $contactsLoader;

  /**
   * @phpstan-param iterable<FundingCaseContactsLoaderInterface> $contactsLoader
   */
  public function __construct(iterable $contactsLoader) {
    $this->contactsLoader = $contactsLoader;
  }

  /**
   * @inheritDoc
   */
  public function getContactsWithAnyPermission(FundingCaseEntity $fundingCase, array $permissions): array {
    $contacts = [];
    foreach ($this->contactsLoader as $contactLoader) {
      $contacts += $contactLoader->getContactsWithAnyPermission($fundingCase, $permissions);
    }

    return $contacts;
  }

}
