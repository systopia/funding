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

namespace Civi\Funding\ApplicationProcess\ActionsContainer;

use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class DefaultApplicationSubmitActionsContainerFactory {

  public static function create(): ApplicationSubmitActionsContainer {
    $submitActionsContainer = new ApplicationSubmitActionsContainer();
    $submitActionsContainer
      // Applicant actions.
      ->add('save', E::ts('Save'))
      ->add('modify', E::ts('Modify'))
      ->add('apply', E::ts('Apply'))
      ->add('withdraw', E::ts('Withdraw'), E::ts('Do you really want to withdraw the application?'))
      ->add('delete', E::ts('Delete'), E::ts('Do you really want to delete the application?'))
      // Reviewer actions.
      ->add('review', E::ts('Start Review'), NULL, ['needsFormData' => FALSE])
      ->add('approve-calculative', E::ts('Approve Calculative'), NULL, ['needsFormData' => FALSE])
      ->add('reject-calculative', E::ts('Reject Calculative'), NULL, ['needsFormData' => FALSE])
      ->add('approve-content', E::ts('Approve Content'), NULL, ['needsFormData' => FALSE])
      ->add('reject-content', E::ts('Reject Content'), NULL, ['needsFormData' => FALSE])
      ->add('request-change', E::ts('Request Change'), NULL, ['needsFormData' => FALSE])
      ->add('approve', E::ts('Approve'), NULL, ['needsFormData' => FALSE])
      ->add('reject', E::ts('Reject'), NULL, ['needsFormData' => FALSE]);

    return $submitActionsContainer;
  }

}
