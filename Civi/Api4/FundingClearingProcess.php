<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingClearingProcess\GetAction;
use Civi\Funding\Api4\Traits\AccessROPermissionsTrait;

/**
 * FundingClearingProcess entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingClearingProcess extends Generic\DAOEntity {

  use AccessROPermissionsTrait;

  public static function get($checkPermissions = TRUE) {
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

}
