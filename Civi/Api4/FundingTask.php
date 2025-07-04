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

use Civi\Api4\Generic\DAOEntity;
use Civi\Funding\Api4\Action\FundingTask\CreateAction;
use Civi\Funding\Api4\Action\FundingTask\DeleteAction;
use Civi\Funding\Api4\Action\FundingTask\GetAction;
use Civi\Funding\Api4\Action\FundingTask\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingTask\UpdateAction;
use Civi\Funding\Api4\Traits\AccessROPermissionsTrait;

/**
 * Note: This extends DAOEntity instead of AbstractEntity so joined fields can
 * be used as filter in SearchKits. This is required because of this code:
 * https://github.com/civicrm/civicrm-core/blob/4021860a8eb814236ead590a1d39b72bb3325d98/Civi/Api4/Generic/Traits/SavedSearchInspectorTrait.php#L152
 */
final class FundingTask extends DAOEntity {

  use AccessROPermissionsTrait;

  public static function autocomplete($checkPermissions = TRUE) {
    throw new \BadMethodCallException('Not implemented');
  }

  public static function create($checkPermissions = TRUE): CreateAction {
    return (new CreateAction())->setCheckPermissions($checkPermissions);
  }

  public static function delete($checkPermissions = TRUE): DeleteAction {
    return (new DeleteAction())->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = TRUE): GetFieldsAction {
    return (new GetFieldsAction())->setCheckPermissions($checkPermissions);
  }

  public static function get($checkPermissions = TRUE): GetAction {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  public static function save($checkPermissions = TRUE) {
    throw new \BadMethodCallException('Not implemented');
  }

  public static function replace($checkPermissions = TRUE) {
    throw new \BadMethodCallException('Not implemented');
  }

  public static function update($checkPermissions = TRUE): UpdateAction {
    return (new UpdateAction())->setCheckPermissions($checkPermissions);
  }

}
