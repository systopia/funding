<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingClearingProcess\GetAction;
use Civi\Funding\Api4\Action\FundingClearingProcess\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingClearingProcess\GetFormAction;
use Civi\Funding\Api4\Action\FundingClearingProcess\SetCalculativeReviewerAction;
use Civi\Funding\Api4\Action\FundingClearingProcess\SetContentReviewerAction;
use Civi\Funding\Api4\Action\FundingClearingProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\FundingClearingProcess\ValidateFormAction;
use Civi\Funding\Api4\Traits\AccessROPermissionsTrait;

/**
 * FundingClearingProcess entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class FundingClearingProcess extends Generic\DAOEntity {

  use AccessROPermissionsTrait {
    permissions as private traitPermissions;
  }

  public static function get($checkPermissions = TRUE) {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = TRUE) {
    return \Civi::service(GetFieldsAction::class)->setCheckPermissions($checkPermissions);
  }

  public static function getForm(): GetFormAction {
    return new GetFormAction();
  }

  public static function validateForm(): ValidateFormAction {
    return new ValidateFormAction();
  }

  public static function submitForm(): SubmitFormAction {
    return new SubmitFormAction();
  }

  public static function setCalculativeReviewer(): SetCalculativeReviewerAction {
    return new SetCalculativeReviewerAction();
  }

  public static function setContentReviewer(): SetContentReviewerAction {
    return new SetContentReviewerAction();
  }

  /**
   * @phpstan-return array<string, array<string|array<string>>>
   */
  public static function permissions(): array {
    $permissions = self::traitPermissions();

    return $permissions + [
      'getForm' => $permissions['get'],
      'validateForm' => $permissions['get'],
      'submitForm' => $permissions['get'],
      'setCalculativeReviewer' => $permissions['get'],
      'setContentReviewer' => $permissions['get'],
    ];
  }

}
