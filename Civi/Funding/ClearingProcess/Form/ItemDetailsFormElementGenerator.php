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

use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTable;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTableRow;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

class ItemDetailsFormElementGenerator {

  /**
   * Creates a JSON forms element that displays details for a finance plan item
   * (from an array) in the clearing form.
   *
   * @param \Civi\RemoteTools\JsonSchema\JsonSchema $applicationPropertySchema
   *   A JSON schema with type "array".
   * @phpstan-param array<string, mixed> $properties
   *   Finance item properties.
   *
   * @return \Civi\RemoteTools\JsonForms\Layout\JsonFormsTable
   *   A JSON Forms table containing the elements' control labels (of non-hidden
   *   controls) as header and one row with the corresponding value from the
   *   given properties. The column containing the financial items' amount is
   *   omitted.
   */
  public function generateDetailsElement(
    JsonSchema $applicationPropertySchema,
    JsonSchema $financePlanItemSchema,
    JsonSchema $applicationFormElement,
    array $properties
  ): JsonFormsTable {
    /** @phpstan-var array<string, JsonSchema> $itemsProperties */
    // @phpstan-ignore-next-line
    $itemsProperties = $applicationPropertySchema['items']['properties']->getKeywords();

    /**
     * @phpstan-var list<JsonSchema>|null $arrayFormControls
     */
    $arrayFormControls = $applicationFormElement['options']['detail']['elements'] ?? NULL;
    if (NULL !== $arrayFormControls) {
      $labelPropertyPairs = $this->handleFormControls($arrayFormControls);
    }
    else {
      // If no control elements are defined use all properties.
      $labelPropertyPairs = $this->handleProperties($itemsProperties);
    }

    $amountProperty = $financePlanItemSchema['amountProperty'];
    $header = [];
    $tableRowElements = [];
    /** @var string $label */
    foreach ($labelPropertyPairs as [$label, $property]) {
      if ($property !== $amountProperty) {
        $value = $properties[$property] ?? '';
        // Actually this should always be true.
        if (is_scalar($value)) {
          $header[] = $label ?? $itemsProperties[$property]['title'] ?? ucfirst($property);
          $tableRowElements[] = new JsonFormsMarkup($this->valueToString($value, $itemsProperties[$property]));
        }
      }
    }

    return new JsonFormsTable($header, [new JsonFormsTableRow($tableRowElements)]);
  }

  /**
   * @param scalar|null $value
   */
  private function valueToString($value, JsonSchema $itemPropertySchema): string {
    /** @phpstan-var list<JsonSchema> $oneOf */
    $oneOf = $itemPropertySchema->getKeywordValueOrDefault('oneOf', []);
    foreach ($oneOf as $oneOfEntry) {
      if ($oneOfEntry->hasKeyword('const') && $oneOfEntry->getKeywordValue('const') === $value) {
        if ($oneOfEntry->hasKeyword('title')) {
          $title = $oneOfEntry->getKeywordValue('title');
          Assert::string($title);

          return $title;
        }

        break;
      }
    }

    if (is_bool($value)) {
      return $value ? E::ts('Yes') : E::ts('No');
    }

    return (string) $value;
  }

  /**
   * @phpstan-param array<JsonSchema> $arrayFormControls
   *
   * @phpstan-return iterable<array{string|null, string}>
   */
  private function handleFormControls(array $arrayFormControls): iterable {
    foreach ($arrayFormControls as $formControl) {
      if ('hidden' !== ($formControl['options']['type'] ?? NULL)) {
        $scope = $formControl['scope'];
        Assert::string($scope);
        /** @var string $property */
        $property = preg_replace('~^#/properties/~', '', $scope);
        /** @var string|null $label */
        $label = $formControl['label'];

        yield [$label, $property];
      }
    }
  }

  /**
   * @phpstan-param array<JsonSchema> $itemsProperties
   *
   * @phpstan-return iterable<array{null, string}>
   */
  private function handleProperties(array $itemsProperties): iterable {
    foreach (array_keys($itemsProperties) as $property) {
      yield [NULL, $property];
    }
  }

}
