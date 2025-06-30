<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseType\MetaData;

use CRM_Funding_ExtensionUtil as E;

final class ReworkApplicationProcessStatuses {

  public static function rework(string $name = 'rework', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('In rework'),
      'icon' => 'fa-spinner',
      'inWork' => TRUE,
    ]);
  }

  public static function reworkRequested(
    string $name = 'rework-requested',
    ?string $label = NULL
  ): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Rework requested'),
      'icon' => 'fa-circle-o',
    ]);
  }

  public static function reworkReviewRequested(
    string $name = 'rework-review-requested',
    ?string $label = NULL
  ): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Rework review requested'),
      'icon' => 'fa-circle-o',
    ]);
  }

  public static function reworkReview(
    string $name = 'rework-review',
    ?string $label = NULL
  ): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => E::ts('Rework in review'),
      'icon' => 'fa-eye',
      'inReview' => TRUE,
    ]);
  }

  /**
   * @phpstan-return non-empty-array<string, ApplicationProcessStatus>
   */
  public static function getAll(): array {
    static $statuses;

    return $statuses ??= [
      'rework' => self::rework(),
      'rework-requested' => self::reworkRequested(),
      'rework-review' => self::reworkReviewRequested(),
      'rework-review-requested' => self::reworkReviewRequested(),
    ];
  }

}
