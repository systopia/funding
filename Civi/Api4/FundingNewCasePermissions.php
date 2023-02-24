<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingNewCasePermissions\GetFieldsAction;
use Civi\Funding\Api4\Traits\AdministerPermissionsTrait;
use Civi\RemoteTools\Api4\Traits\EntityNameTrait;

/**
 * FundingNewCasePermissions entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingNewCasePermissions extends Generic\DAOEntity {

  use AdministerPermissionsTrait;

  use EntityNameTrait;

  public static function getFields($checkPermissions = TRUE) {
    return \Civi::service(GetFieldsAction::class)->setCheckPermissions($checkPermissions);
  }

}
