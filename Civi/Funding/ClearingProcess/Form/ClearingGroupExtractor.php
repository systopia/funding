<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\ClearingProcess\Form\Container\ClearingItemsGroup;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Webmozart\Assert\Assert;

class ClearingGroupExtractor {

  private bool $root = TRUE;

  /**
   * Returns for the given scopes the control schemas grouped in the way they
   * shall be grouped in the clearing form. If the group label is NULL, the item
   * (there's only one in that case) shall be in a group labeled by the item
   * label in the clearing form. The order depends on the order of the controls
   * in the UI schema, not on the order of the given scopes.
   *
   * The group label is the label of the closest layout element with a non-empty
   * label. It is NULL, if the next layout element with a non-empty label is a
   * Category or the root element.
   *
   * @phpstan-param array<string> $scopes
   *   Scopes of the items to be cleared.
   *
   * @phpstan-return list<ClearingItemsGroup>
   */
  public function extractGroups(JsonFormsElement $uiSchema, array $scopes): array {
    $this->root = TRUE;

    return array_values($this->doExtract($uiSchema, $scopes, NULL));
  }

  /**
   * @phpstan-param array<string> $scopes
   *   Found scopes will be removed.
   * @param \Civi\RemoteTools\JsonSchema\JsonSchema|null $ancestorLayout
   *   An ancestor layout containing the group label if $layout has no label.
   *
   * @phpstan-return array<string, ClearingItemsGroup>
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  private function doExtract(
    JsonSchema $layout,
    array &$scopes,
    ?JsonSchema $ancestorLayout
  ): array {
    //phpcs:enable
    if (!$layout->hasKeyword('elements') || $layout->hasKeyword('scope')) {
      return [];
    }

    if ('Category' === $layout->getKeywordValue('type') || $this->root) {
      $ancestorLayout = NULL;
      $this->root = FALSE;
    }
    elseif ('' !== $layout->getKeywordValueOrDefault('label', '')) {
      $ancestorLayout = $layout;
    }

    if (NULL === $ancestorLayout) {
      $groupKey = NULL;
      $groupLabel = NULL;
    }
    else {
      $groupKey = spl_object_hash($ancestorLayout);
      /** @var string $groupLabel */
      $groupLabel = $ancestorLayout->getKeywordValue('label');
    }

    $result = [];
    $elements = $layout->getKeywordValue('elements');
    Assert::isArray($elements);
    foreach ($elements as $element) {
      if ([] === $scopes) {
        break;
      }

      Assert::isInstanceOf($element, JsonSchema::class);
      if ($element->hasKeyword('scope')) {
        $scope = $element->getKeywordValue('scope');
        $scopePos = array_search($scope, $scopes, TRUE);
        if (FALSE !== $scopePos) {
          /** @var string $scope */
          unset($scopes[$scopePos]);

          if (NULL === $ancestorLayout) {
            $groupKey = spl_object_hash($element);
          }

          $result[$groupKey] ??= new ClearingItemsGroup($groupLabel);
          $result[$groupKey]->elements[$scope] = $element;
        }
      }
      else {
        $result += $this->doExtract($element, $scopes, $ancestorLayout);
      }
    }

    return $result;
  }

}
