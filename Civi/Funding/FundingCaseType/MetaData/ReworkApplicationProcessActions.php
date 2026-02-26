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

final class ReworkApplicationProcessActions {

  public static function requestRework(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'request-rework',
      'label' => $label ?? E::ts('Request rework'),
    ]);
  }

  public static function withdrawReworkRequest(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'withdraw-rework-request',
      'label' => $label ?? E::ts('Withdraw rework request'),
    ]);
  }

  public static function withdrawChange(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'withdraw-change',
      'label' => $label ?? E::ts('Withdraw change'),
      'restore' => TRUE,
    ]);
  }

  public static function applyRework(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'apply-rework',
      'label' => $label ?? E::ts('Apply rework'),
    ]);
  }

  public static function withdrawRework(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'withdraw-rework',
      'label' => $label ?? E::ts('Withdraw rework'),
    ]);
  }

  public static function approveReworkRequest(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'approve-rework-request',
      'label' => $label ?? E::ts('Approve Rework Request'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function rejectReworkRequest(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'reject-rework-request',
      'label' => $label ?? E::ts('Reject Rework Request'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function approveChange(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'approve-change',
      'label' => $label ?? E::ts('Approve'),
      'batchPossible' => TRUE,
    ]);
  }

  public static function rejectChange(?string $label = NULL): ApplicationProcessAction {
    return new ApplicationProcessAction([
      'name' => 'reject-change',
      'label' => $label ?? E::ts('Reject'),
      'batchPossible' => TRUE,
      'restore' => TRUE,
    ]);
  }

  /**
   * @return non-empty-array<string, ApplicationProcessAction>
   */
  public static function getAll(): array {
    static $actions;

    return $actions ??= [
      // Applicant actions.
      'request-rework' => self::requestRework(),
      'withdraw-rework-request' => self::withdrawReworkRequest(),
      'withdraw-change' => self::withdrawChange(),
      'apply-rework' => self::applyRework(),
      'withdraw-rework' => self::withdrawRework(),
      // Reviewer actions.
      'approve-rework-request' => self::approveReworkRequest(),
      'reject-rework-request' => self::rejectReworkRequest(),
      'approve-change' => self::approveChange(),
      'reject-change' => self::rejectChange(),
    ];
  }

}
