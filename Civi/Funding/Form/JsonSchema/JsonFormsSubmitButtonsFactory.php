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

/**
 * @phpstan-type submitActionT array{label: string, confirm?: string|null}
 */
final class JsonFormsSubmitButtonsFactory {

  /**
   * @phpstan-param submitActionT $action
   */
  public static function createButton(string $actionName, array $action): JsonFormsSubmitButton {
    return new JsonFormsSubmitButton(
      '#/properties/_action',
      $actionName,
      $action['label'],
      $action['confirm'] ?? NULL
    );
  }

  /**
   * @phpstan-param array<string, submitActionT> $actions
   *   Key is the action name.
   *
   * @phpstan-return array<JsonFormsSubmitButton>
   */
  public static function createButtons(array $actions): array {
    $buttons = [];
    foreach ($actions as $name => $action) {
      $buttons[] = new JsonFormsSubmitButton(
        '#/properties/_action',
        $name,
        $action['label'],
        $action['confirm'] ?? NULL
      );
    }

    return $buttons;
  }

}
