<?php
declare(strict_types = 1);

namespace Civi\Api4\Action;

use Civi\Api4\Generic\DAOGetFieldsAction;

class RemoteDAOGetFieldsAction extends DAOGetFieldsAction {

  private array $excludedFields = [];

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
        'data_type' => \CRM_Core_DAO::SERIALIZE_NONE != $field->getSerialize() ? 'Array' : $field->getDataType(),
        'nullable' => $field->getNullable(),
        'operators' => $field->operators,
      ];
      if ($this->loadOptions) {
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

}
