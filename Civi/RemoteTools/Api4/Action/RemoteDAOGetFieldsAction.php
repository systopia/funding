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

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Service\Spec\FieldSpec;

class RemoteDAOGetFieldsAction extends DAOGetFieldsAction {

  /**
   * @var string[]
   */
  private array $excludedFields = [];

  /**
   * @var string[]|null
   */
  private ?array $includedFields = NULL;

  /**
   * @param string[] $excludedFields
   */
  public function setExcludedFields(array $excludedFields): self {
    $this->excludedFields = $excludedFields;

    return $this;
  }

  /**
   * @param string[]|null $includedFields
   */
  public function setIncludedFields(?array $includedFields): self {
    $this->includedFields = $includedFields;

    return $this;
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function fields(): array {
    return [
      [
        'name' => 'name',
        'data_type' => 'String',
        'description' => 'Unique field identifier',
      ],
      [
        'name' => 'options',
        'data_type' => 'Array',
        'default_value' => FALSE,
      ],
      [
        'name' => 'readonly',
        'data_type' => 'Boolean',
        'description' => 'True for auto-increment, calculated, or otherwise non-editable fields.',
        'default_value' => FALSE,
      ],
      [
        'name' => 'operators',
        'data_type' => 'Array',
        'description' => 'If set, limits the operators that can be used on this field for "get" actions.',
      ],
      [
        'name' => 'data_type',
        'default_value' => 'String',
        'options' => [
          'Array' => 'Array',
          'Boolean' => 'Boolean',
          'Date' => 'Date',
          'Float' => 'Float',
          'Integer' => 'Integer',
          'String' => 'String',
          'Text' => 'Text',
          'Timestamp' => 'Timestamp',
        ],
      ],
      [
        'name' => 'nullable',
        'description' => 'Whether a null value is allowed in this field',
        'data_type' => 'Boolean',
        'default_value' => TRUE,
      ],
      [
        'name' => 'description',
        'data_type' => 'String',
        'description' => 'Explanation of the purpose of the field',
      ],
    ];
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  protected function specToArray($fields): array {
    $fieldArray = [];

    foreach ($fields as $field) {
      if (!$this->isFieldInResult($field->getName())) {
        continue;
      }

      $fieldDef = [
        'name' => $field->getName(),
        'readonly' => $field->readonly,
        'description' => $field->getDescription(),
        'data_type' => $this->isFieldSerialized($field) ? 'Array' : $field->getDataType(),
        'nullable' => $field->getNullable(),
        'operators' => $field->operators,
      ];
      if (FALSE !== $this->loadOptions) {
        $fieldDef['options'] = $field->getOptions($this->values, $this->loadOptions, $this->checkPermissions);
      }
      $fieldArray[] = $fieldDef;
    }

    return $fieldArray;
  }

  private function isFieldInResult(string $fieldName): bool {
    if (NULL !== $this->includedFields) {
      return in_array($fieldName, $this->includedFields, TRUE);
    }

    return !in_array($fieldName, $this->excludedFields, TRUE);
  }

  private function isFieldSerialized(FieldSpec $field): bool {
    return NULL !== $field->getSerialize() && 0 !== $field->getSerialize();
  }

}
