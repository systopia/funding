<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Traits\AccessROAdministerRWPermissionsTrait;

/**
 * FundingCasePermissionsCache entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
class FundingCasePermissionsCache extends Generic\DAOEntity {

  use AccessROAdministerRWPermissionsTrait;

}
