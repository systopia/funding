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

namespace Civi\Funding;

use CRM_Funding_ExtensionUtil as E;

final class FundingPseudoConstants {

  /**
   * @return array<string, string>
   */
  public static function getApplicationProcessStatus(): array {
    return [
      'new' => E::ts('New'),
      'draft' => E::ts('Draft'),
      'withdrawn' => E::ts('Withdrawn'),
      'applied' => E::ts('Applied'),
      'review' => E::ts('In review'),
      'rejected' => E::ts('Rejected'),
      'pre-approved' => E::ts('Pre-approved'),
      'approved' => E::ts('Approved'),
      'change' => E::ts('Change'),
      'change-review-requested' => E::ts('Change review requested'),
      'change-review' => E::ts('Change in review'),
    ];
  }

  /**
   * @return array<string, string>
   */
  public static function getFundingCaseStatus(): array {
    return [
      'open' => E::ts('Open'),
      'closed' => E::ts('Closed'),
    ];
  }

  /**
   * @return array<string, string>
   */
  public static function getFundingProgramRelationshipTypes(): array {
    return [
      'adoptable' => E::ts('Applications adoptable'),
    ];
  }

  /**
   * @return array<string, string>
   */
  public static function getRelationshipTypeDirections(): array {
    return [
      'a_b' => E::ts('Relationship from a to b'),
      'b_a' => E::ts('Relationship from b to a'),
    ];
  }

}
