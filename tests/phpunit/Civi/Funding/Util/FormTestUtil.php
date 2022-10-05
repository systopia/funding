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

namespace Civi\Funding\Util;

use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonForms\JsonFormsElement;
use Civi\RemoteTools\Form\JsonForms\JsonFormsLayout;

final class FormTestUtil {

  public static function getFirstControlWithScope(string $scope, JsonFormsElement $uiSchema): ?JsonFormsControl {
    $elementQueue = new \SplQueue();
    $elementQueue->enqueue($uiSchema);
    while (!$elementQueue->isEmpty()) {
      $element = $elementQueue->dequeue();
      if ($element instanceof JsonFormsControl) {
        if ($scope === $element->getScope()) {
          return $element;
        }
      }
      elseif ($element instanceof JsonFormsLayout) {
        array_map(fn ($element) => $elementQueue->push($element), $element->getElements());
      }
    }

    return NULL;
  }

  /**
   * @phpstan-return array<JsonFormsControl>
   */
  public static function getControlsWithScope(string $scope, JsonFormsElement $uiSchema): array {
    $controls = [];
    $elementQueue = new \SplQueue();
    $elementQueue->enqueue($uiSchema);
    while (!$elementQueue->isEmpty()) {
      $element = $elementQueue->dequeue();
      if ($element instanceof JsonFormsControl) {
        if ($scope === $element->getScope()) {
          $controls[] = $element;
        }
      }
      elseif ($element instanceof JsonFormsLayout) {
        array_map(fn ($element) => $elementQueue->push($element), $element->getElements());
      }
    }

    return $controls;
  }

}
