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

namespace Civi\Funding\Api4\Action\FundingProgramContactRelation;

use Civi\Api4\FundingProgram;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Funding\Api4\Action\Traits\PermissionsSelectTrait;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;

final class GetFieldsAction extends DAOGetFieldsAction {

  use PermissionsSelectTrait;

  private PossiblePermissionsLoaderInterface $possiblePermissionsLoader;

  public function __construct(PossiblePermissionsLoaderInterface $possiblePermissionsLoader) {
    parent::__construct(FundingProgramContactRelation::getEntityName(), 'getFields');
    $this->possiblePermissionsLoader = $possiblePermissionsLoader;
  }

  /**
   * @phpstan-return array<string>
   */
  protected function getPossiblePermissions(): array {
    return $this->possiblePermissionsLoader->getPermissions(FundingProgram::getEntityName());
  }

}
