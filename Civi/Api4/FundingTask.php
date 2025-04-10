<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Api4\Generic\AbstractEntity;
use Civi\Funding\Api4\Action\FundingTask\CreateAction;
use Civi\Funding\Api4\Action\FundingTask\DeleteAction;
use Civi\Funding\Api4\Action\FundingTask\GetAction;
use Civi\Funding\Api4\Action\FundingTask\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingTask\UpdateAction;
use Civi\Funding\Api4\Traits\AccessROPermissionsTrait;

final class FundingTask extends AbstractEntity {

  use AccessROPermissionsTrait;

  public static function create(bool $checkPermissions = TRUE): CreateAction {
    return (new CreateAction())->setCheckPermissions($checkPermissions);
  }

  public static function delete(bool $checkPermissions = TRUE): DeleteAction {
    return (new DeleteAction())->setCheckPermissions($checkPermissions);
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new GetFieldsAction();
  }

  public static function get(bool $checkPermissions = TRUE): GetAction {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  public static function update(bool $checkPermissions = TRUE): UpdateAction {
    return (new UpdateAction())->setCheckPermissions($checkPermissions);
  }

}
