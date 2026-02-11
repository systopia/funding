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

namespace Civi\Funding\Form\MappedData;

use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

/**
 * Loads values from tagged data that are mapped to entity fields. The values
 * have to be tagged like this in the JSON schema (see keyword "$tag"):
 * {
 *  "fieldName": "some_field",
 *  "multiple": false,
 *  "replace": true
 * }
 *
 * "multiple" and "replace" are optional the values above are the defaults.
 *
 * If "replace" is false, a previous value with the same field name is not
 * replaced.
 *
 * If "multiple" is true, the value is put into an array. If there's already
 * a value where "multiple" was true as well, the arrays of the previous value
 * and the current value are merged. If there's already a value where "multiple"
 * was false, the behavior depends on "replace". Useful if this tagged is used
 * for array items.
 */
final class MappedDataLoader {

  /**
   * @phpstan-return array<string, mixed>
   *   Field names mapped to data.
   */
  public function getMappedData(TaggedDataContainerInterface $taggedData): array {
    $fieldData = [];
    $multipleFields = [];

    foreach ($taggedData->getByTag('mapToField') as $dataPointer => $data) {
      /** @var object{fieldName: string, multiple?: bool, replace?: bool}&\stdClass $extra */
      $extra = $taggedData->getExtra('mapToField', $dataPointer);
      $fieldName = $extra->fieldName;
      $multiple = $extra->multiple ?? FALSE;
      $replace = $extra->replace ?? TRUE;

      if ($multiple) {
        $data = [$data];
      }

      if (!array_key_exists($fieldName, $fieldData)) {
        $fieldData[$fieldName] = $data;
        $multipleFields[$fieldName] = $multiple;
      }
      elseif ($multiple && $multipleFields[$fieldName]) {
        // @phpstan-ignore-next-line $fieldData[$fieldName] contains array.
        $fieldData[$fieldName] = array_merge($fieldData[$fieldName], $data);
      }
      elseif ($replace) {
        $fieldData[$fieldName] = $data;
        $multipleFields[$fieldName] = $multiple;
      }
    }

    return $fieldData;
  }

}
