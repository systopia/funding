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

namespace Civi\Funding\Form;

use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class DefaultApplicationSubmitActionsContainerFactory {

  public static function create(): SubmitActionsContainer {
    $submitActionsContainer = new SubmitActionsContainer();
    $submitActionsContainer
      ->add('save', E::ts('Save'))
      ->add('modify', E::ts('Modify'))
      ->add('apply', E::ts('Apply'))
      ->add('withdraw', E::ts('Withdraw'), E::ts('Do you really want to withdraw the application?'))
      ->add('delete', E::ts('Delete'), E::ts('Do you really want to delete the application?'));

    return $submitActionsContainer;
  }

}
