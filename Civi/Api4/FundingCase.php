<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\Funding\Api4\Action\FundingCase\ApproveAction;
use Civi\Funding\Api4\Action\FundingCase\CreateDrawdownsAction;
use Civi\Funding\Api4\Action\FundingCase\FinishClearingAction;
use Civi\Funding\Api4\Action\FundingCase\GetAction;
use Civi\Funding\Api4\Action\FundingCase\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingCase\GetPossibleActionsAction;
use Civi\Funding\Api4\Action\FundingCase\GetPossibleRecipientsAction;
use Civi\Funding\Api4\Action\FundingCase\GetSearchTasksAction;
use Civi\Funding\Api4\Action\FundingCase\RecreateTransferContractAction;
use Civi\Funding\Api4\Action\FundingCase\RejectAction;
use Civi\Funding\Api4\Action\FundingCase\ResetPermissionsAction;
use Civi\Funding\Api4\Action\FundingCase\SetNotificationContactsAction;
use Civi\Funding\Api4\Action\FundingCase\SetRecipientContactAction;
use Civi\Funding\Api4\Action\FundingCase\UpdateAmountApprovedAction;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Api4\Traits\AccessPermissionsTrait;

/**
 * FundingCase entity.
 *
 * Provided by the Funding Program Manager extension.
 */
final class FundingCase extends Generic\DAOEntity {

  use AccessPermissionsTrait {
    permissions as private traitPermissions;
  }

  public static function approve(bool $checkPermissions = TRUE): ApproveAction {
    return (new ApproveAction())->setCheckPermissions($checkPermissions);
  }

  public static function createDrawdowns(bool $checkPermissions = TRUE): CreateDrawdownsAction {
    return (new CreateDrawdownsAction())->setCheckPermissions($checkPermissions);
  }

  public static function finishClearing(bool $checkPermissions = TRUE): FinishClearingAction {
    return (new FinishClearingAction())->setCheckPermissions($checkPermissions);
  }

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingCase\GetAction
   */
  public static function get($checkPermissions = TRUE) {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingCase\GetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new GetFieldsAction())->setCheckPermissions($checkPermissions);
  }

  public static function getSearchTasks(bool $checkPermissions = TRUE): GetSearchTasksAction {
    return (new GetSearchTasksAction())->setCheckPermissions($checkPermissions);
  }

  public static function getPossibleActions(bool $checkPermissions = TRUE): GetPossibleActionsAction {
    return (new GetPossibleActionsAction())->setCheckPermissions($checkPermissions);
  }

  public static function getPossibleRecipients(bool $checkPermissions = TRUE): GetPossibleRecipientsAction {
    return (new GetPossibleRecipientsAction())->setCheckPermissions($checkPermissions);
  }

  public static function recreateTransferContract(bool $checkPermissions = TRUE): RecreateTransferContractAction {
    return (new RecreateTransferContractAction())->setCheckPermissions($checkPermissions);
  }

  public static function reject(bool $checkPermissions = TRUE): RejectAction {
    return (new RejectAction())->setCheckPermissions($checkPermissions);
  }

  public static function resetPermissions(bool $checkPermissions = TRUE): ResetPermissionsAction {
    return (new ResetPermissionsAction())->setCheckPermissions($checkPermissions);
  }

  public static function setNotificationContacts(bool $checkPermissions = TRUE): SetNotificationContactsAction {
    return (new SetNotificationContactsAction())->setCheckPermissions($checkPermissions);
  }

  public static function setRecipientContact(bool $checkPermissions = TRUE): SetRecipientContactAction {
    return (new SetRecipientContactAction())->setCheckPermissions($checkPermissions);
  }

  public static function updateAmountApproved(bool $checkPermissions = TRUE): UpdateAmountApprovedAction {
    return (new UpdateAmountApprovedAction())->setCheckPermissions($checkPermissions);

  }

  /**
   * @return array<string, array<string|string[]>>
   */
  public static function permissions(): array {
    return ['resetPermissions' => [Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]]
      + self::traitPermissions();
  }

}
