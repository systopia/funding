<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingPayoutProcess\GetAction;
use Civi\Funding\Api4\Traits\AccessPermissionsTrait;

/**
 * FundingPayoutProcess entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
class FundingPayoutProcess extends Generic\DAOEntity {

  use AccessPermissionsTrait;

  public static function get($checkPermissions = TRUE) {
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

}
