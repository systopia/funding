<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseType\MetaData;

use CRM_Funding_ExtensionUtil as E;

final class DefaultApplicationProcessActions {

  public static function save(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Save'),
    ]);
  }

  public static function modify(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Modify'),
    ]);
  }

  public static function apply(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Apply'),
    ]);
  }

  public static function withdraw(?string $label = NULL, ?string $confirmMessage = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Withdraw'),
      'confirmMessage' => $confirmMessage ?? E::ts('Do you really want to withdraw the application?'),
    ]);
  }

  public static function delete(?string $label = NULL, ?string $confirmMessage = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Delete'),
      'confirmMessage' => $confirmMessage ?? E::ts('Do you really want to delete the application?'),
      'delete' => TRUE,
    ]);
  }

  public static function review(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Start Review'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function approveCalculative(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'approve-calculative',
      'label' => $label ?? E::ts('Approve Calculative'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function rejectCalculative(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'reject-calculative',
      'label' => $label ?? E::ts('Reject Calculative'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function approveContent(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'approve-content',
      'label' => $label ?? E::ts('Approve Content'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function rejectContent(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'reject-content',
      'label' => $label ?? E::ts('Reject Content'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function requestChange(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'request-change',
      'label' => $label ?? E::ts('Request Change'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function approve(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Approve'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function reject(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => __FUNCTION__,
      'label' => $label ?? E::ts('Reject'),
      'batchPossible' => TRUE,
    ]);
  }

  /**
   * @return non-empty-array<string, ApplicationProcessAction>
   */
  public static function getAll(): array {
    static $actions;

    return $actions ??= [
      // Applicant actions.
      'save' => self::save(),
      'modify' => self::modify(),
      'apply' => self::apply(),
      'withdraw' => self::withdraw(),
      'delete' => self::delete(),
      // Reviewer actions.
      'review' => self::review(),
      'approve-calculative' => self::approveCalculative(),
      'reject-calculative' => self::rejectCalculative(),
      'approve-content' => self::approveContent(),
      'reject-content' => self::rejectContent(),
      'request-change' => self::requestChange(),
      'approve' => self::approve(),
      'reject' => self::reject(),
    ];
  }

}
