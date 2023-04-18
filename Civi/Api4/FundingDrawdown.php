<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingDrawdown\GetAction;
use Civi\Funding\Api4\Traits\AccessPermissionsTrait;
use Civi\RemoteTools\Api4\Traits\EntityNameTrait;

/**
 * FundingDrawdown entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingDrawdown extends Generic\DAOEntity {

  use AccessPermissionsTrait;

  use EntityNameTrait;

  public static function get($checkPermissions = TRUE) {
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

}
