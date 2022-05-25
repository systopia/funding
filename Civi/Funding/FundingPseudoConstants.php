<?php
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
  public function getFundingProgramRelationshipTypes(): array {
    return [
      'adoptable' => E::ts('Applications adoptable'),
    ];
  }

}
