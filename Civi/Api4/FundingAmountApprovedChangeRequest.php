<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

use Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\ApproveAction;
use Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\ApprovePartialAction;
use Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\GetAction;
use Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\RejectAction;

/**
 * FundingAmountApprovedChangeRequest entity.
 */
final class FundingAmountApprovedChangeRequest extends Generic\DAOEntity {

  /**
   * @param bool $checkPermissions
   *
   * @return \Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\ApproveAction
   */
  public static function approve(bool $checkPermissions = TRUE): ApproveAction {
    return (new ApproveAction())->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   *
   * @return \Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\ApprovePartialAction
   */
  public static function approvePartial(bool $checkPermissions = TRUE): ApprovePartialAction {
    return (new ApprovePartialAction())->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   *
   * @return \Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\RejectAction
   */
  public static function reject(bool $checkPermissions = TRUE): RejectAction {
    return (new RejectAction())->setCheckPermissions($checkPermissions);
  }

  public static function get($checkPermissions = TRUE): GetAction {
    return (new GetAction())->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = TRUE): GetFieldsAction {
    return (new GetFieldsAction())->setCheckPermissions($checkPermissions);
  }

}
