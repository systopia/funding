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

final class DefaultApplicationProcessStatuses {

  public static function applied(string $name = 'applied', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Applied'),
      'icon' => 'fa-circle-o',
    ]);
  }

  public static function complete(string $name = 'complete', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Complete'),
      'icon' => 'fa-check-circle',
      'eligible' => TRUE,
      'final' => TRUE,
    ]);
  }

  public static function draft(string $name = 'draft', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Draft'),
      'icon' => 'fa-spinner',
      'inWork' => TRUE,
    ]);
  }

  public static function eligible(string $name = 'eligible', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Eligible'),
      'icon' => 'fa-check-circle-o',
      'iconColor' => '#56ab41',
      'eligible' => TRUE,
    ]);
  }

  public static function new(string $name = 'new', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('New'),
      'icon' => 'fa-plus-circle',
      'inWork' => TRUE,
    ]);
  }

  public static function rejected(string $name = 'rejected', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Rejected'),
      'icon' => 'fa-eye',
      'iconColor' => '#d65050',
      'rejected' => TRUE,
    ]);
  }

  public static function review(string $name = 'review', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('In review'),
      'icon' => 'fa-eye',
      'inReview' => TRUE,
    ]);
  }

  public static function withdrawn(string $name = 'withdrawn', ?string $label = NULL): ApplicationProcessStatus {
    return new ApplicationProcessStatus([
      'name' => $name,
      'label' => $label ?? E::ts('Withdrawn'),
      'icon' => 'fa-arrow-circle-o-left',
      'withdrawn' => TRUE,
    ]);
  }

  /**
   * @phpstan-return non-empty-array<string, ApplicationProcessStatus>
   */
  public static function getAll(): array {
    static $statuses;

    return $statuses ??= [
      'applied' => self::applied(),
      'complete' => self::complete(),
      'draft' => self::draft(),
      'eligible' => self::eligible(),
      'new' => self::new(),
      'rejected' => self::rejected(),
      'review' => self::review(),
      'withdrawn' => self::withdrawn(),
    ];
  }

}
