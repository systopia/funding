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

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\RemoteTools\Api4\Action\Traits\PermissionsGetFieldsActionTrait;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;

final class GetFieldsAction extends DAOGetFieldsAction {

  use PermissionsGetFieldsActionTrait {
    PermissionsGetFieldsActionTrait::getRecords as getRecordsWithPermissions;
  }

  private PossiblePermissionsLoaderInterface $possiblePermissionsLoader;

  public function __construct(PossiblePermissionsLoaderInterface $possiblePermissionsLoader) {
    parent::__construct(FundingCase::getEntityName(), 'getFields');
    $this->possiblePermissionsLoader = $possiblePermissionsLoader;
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge($this->getRecordsWithPermissions(), [
      [
        'name' => 'transfer_contract_uri',
        'description' => 'URI to download the transfer contract or null if no transfer contract exists.',
        'type' => 'Custom',
        'data_type' => 'String',
        'readonly' => TRUE,
        'required' => FALSE,
      ],
    ]);
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function getPossiblePermissions(): array {
    return $this->possiblePermissionsLoader->getFilteredPermissions($this->getEntityName());
  }

}
