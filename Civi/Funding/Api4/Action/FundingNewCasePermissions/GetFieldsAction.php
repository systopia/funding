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

namespace Civi\Funding\Api4\Action\FundingNewCasePermissions;

use Civi\Api4\FundingCase;
use Civi\Api4\FundingNewCasePermissions;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Funding\Api4\Action\Traits\PermissionsSelectTrait;
use Civi\Funding\Api4\Action\Traits\PossiblePermissionsLoaderTrait;
use Civi\Funding\Permission\PossiblePermissionsLoaderInterface;

final class GetFieldsAction extends DAOGetFieldsAction {

  use PermissionsSelectTrait;

  use PossiblePermissionsLoaderTrait;

  public function __construct(?PossiblePermissionsLoaderInterface $possiblePermissionsLoader = NULL) {
    parent::__construct(FundingNewCasePermissions::getEntityName(), 'getFields');
    $this->_possiblePermissionsLoader = $possiblePermissionsLoader;
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function getPossiblePermissions(): array {
    return $this->getPossiblePermissionsLoader()->getPermissions(FundingCase::getEntityName());
  }

}
