<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingClearingResourcesItem\GetAction;
use Civi\Funding\Api4\Action\Generic\ClearingItem\GetFieldsAction;
use Civi\Funding\Api4\Traits\AccessROPermissionsTrait;

/**
 * FundingClearingResourcesItem entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingClearingResourcesItem extends Generic\DAOEntity {

  use AccessROPermissionsTrait;

  public static function get($checkPermissions = TRUE) {
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = TRUE) {
    return (new GetFieldsAction(static::getEntityName()))->setCheckPermissions($checkPermissions);
  }

}
