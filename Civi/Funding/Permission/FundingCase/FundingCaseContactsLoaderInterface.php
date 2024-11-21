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

interface FundingCaseContactsLoaderInterface {

  /**
   * @phpstan-param non-empty-list<string> $permissions
   *
   * @phpstan-return array<int, array<string, mixed>>
   *   Contacts that have any of the given permissions indexed by id.
   */
  public function getContactsWithAnyPermission(FundingCaseEntity $fundingCase, array $permissions): array;

}
