<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingDrawdown\AcceptAction;
use Civi\Funding\Api4\Action\FundingDrawdown\CreateAction;
use Civi\Funding\Api4\Action\FundingDrawdown\GetAction;
use Civi\Funding\Api4\Action\FundingDrawdown\RejectAction;
use Civi\Funding\Api4\Action\FundingDrawdown\SaveAction;
use Civi\Funding\Api4\Action\FundingDrawdown\UpdateAction;
use Civi\Funding\Api4\Permissions;
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

  use AccessPermissionsTrait {
    permissions as private traitPermissions;
  }

  use EntityNameTrait;

  public static function accept(bool $checkPermissions = TRUE): AcceptAction {
    return \Civi::service(AcceptAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function create($checkPermissions = TRUE) {
    return \Civi::service(CreateAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function get($checkPermissions = TRUE) {
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function reject(bool $checkPermissions = TRUE): RejectAction {
    return \Civi::service(RejectAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function save($checkPermissions = TRUE) {
    return \Civi::service(SaveAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function update($checkPermissions = TRUE) {
    return \Civi::service(UpdateAction::class)->setCheckPermissions($checkPermissions);
  }

  /**
   * @return array<string, array<string|string[]>>
   */
  public static function permissions(): array {
    // Deletion is normally done via reject.
    return ['delete' => [Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]]
      + self::traitPermissions();
  }

}
