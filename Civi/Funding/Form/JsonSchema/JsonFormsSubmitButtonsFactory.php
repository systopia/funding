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

namespace Civi\Funding\Form\JsonSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;

final class JsonFormsSubmitButtonsFactory {

  /**
   * phpcs:ignore Generic.Files.LineLength.TooLong
   * @param array<\Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction>|array<\Civi\Funding\FundingCaseType\MetaData\FundingCaseAction> $actions
   *
   * @return list<JsonFormsSubmitButton>
   */
  public static function createButtons(array $actions): array {
    return array_values(array_map(fn ($action) => new JsonFormsSubmitButton(
      '#/properties/_action',
      $action->getName(),
      $action->getLabel(),
      $action->getConfirmMessage()
    ), $actions));
  }

}
