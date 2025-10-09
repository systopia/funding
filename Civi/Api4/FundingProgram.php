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

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingProgram\GetAmountApprovedAction;
use Civi\Funding\Api4\Action\FundingProgram\GetAction;
use Civi\Funding\Api4\Action\FundingProgram\GetFieldsAction;
use Civi\Funding\Api4\Traits\AccessPermissionsTrait;

/**
 * FundingProgram entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingProgram extends Generic\DAOEntity {

  use AccessPermissionsTrait;

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingProgram\GetAction
   */
  public static function get($checkPermissions = TRUE) {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  /**
   * This action will return a value even if the permissions do not allow to
   * access the funding program itself, if there's at least one case allowed to
   * access with the given funding program.
   */
  public static function getAmountApproved(bool $checkPermissions = TRUE): GetAmountApprovedAction {
    return new GetAmountApprovedAction();
  }

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingProgram\GetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new GetFieldsAction())->setCheckPermissions($checkPermissions);
  }

}
