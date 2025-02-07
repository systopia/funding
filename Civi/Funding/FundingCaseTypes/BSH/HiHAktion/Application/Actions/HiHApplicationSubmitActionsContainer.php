<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsContainer\AbstractApplicationSubmitActionsContainer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class HiHApplicationSubmitActionsContainer extends AbstractApplicationSubmitActionsContainer {

  use HiHSupportedFundingCaseTypesTrait;

  public function __construct() {
    $this
      // Applicant actions.
      ->add('save', E::ts('Save'))
      ->add('modify', E::ts('Modify'))
      ->add('apply', E::ts('Apply'))
      ->add('withdraw', E::ts('Withdraw'), E::ts('Do you really want to withdraw the application?'))
      ->add('delete', E::ts('Delete'), E::ts('Do you really want to delete the application?'))
      // Reviewer actions.
      ->add('review', E::ts('Start Review'), NULL, ['needsFormData' => FALSE])
      ->add('release', 'Für Beirat freigeben', NULL, ['needsFormData' => FALSE])
      ->add('request-change', E::ts('Request Change'), NULL, ['needsFormData' => FALSE])
      // Reviewer actions.
      ->add('reject', E::ts('Reject'), NULL, ['needsFormData' => FALSE])
      // Admin actions
      ->add('re-apply', 'Zurück zu "beantragt"', NULL, ['needsFormData' => FALSE])
      ->add('re-release', 'Erneut für Beirat freigeben', NULL, ['needsFormData' => FALSE]);
  }

}
