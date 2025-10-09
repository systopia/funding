<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingDrawdown\AcceptAction;
use Civi\Funding\Api4\Action\FundingDrawdown\AcceptMultipleAction;
use Civi\Funding\Api4\Action\FundingDrawdown\CreateAction;
use Civi\Funding\Api4\Action\FundingDrawdown\GetAction;
use Civi\Funding\Api4\Action\FundingDrawdown\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingDrawdown\RejectAction;
use Civi\Funding\Api4\Action\FundingDrawdown\RejectMultipleAction;
use Civi\Funding\Api4\Action\FundingDrawdown\SaveAction;
use Civi\Funding\Api4\Action\FundingDrawdown\UpdateAction;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Api4\Traits\AccessPermissionsTrait;

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

  public static function accept(bool $checkPermissions = TRUE): AcceptAction {
    return (new AcceptAction())->setCheckPermissions($checkPermissions);
  }

  public static function acceptMultiple(bool $checkPermissions = TRUE): AcceptMultipleAction {
    return (new AcceptMultipleAction())->setCheckPermissions($checkPermissions);
  }

  public static function create($checkPermissions = TRUE) {
    return (new CreateAction())->setCheckPermissions($checkPermissions);
  }

  public static function get($checkPermissions = TRUE) {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = TRUE) {
    return (new GetFieldsAction())->setCheckPermissions($checkPermissions);
  }

  public static function reject(bool $checkPermissions = TRUE): RejectAction {
    return (new RejectAction())->setCheckPermissions($checkPermissions);
  }

  public static function rejectMultiple(bool $checkPermissions = TRUE): RejectMultipleAction {
    return (new RejectMultipleAction())->setCheckPermissions($checkPermissions);
  }

  public static function save($checkPermissions = TRUE) {
    return (new SaveAction())->setCheckPermissions($checkPermissions);
  }

  public static function update($checkPermissions = TRUE) {
    return (new UpdateAction())->setCheckPermissions($checkPermissions);
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
