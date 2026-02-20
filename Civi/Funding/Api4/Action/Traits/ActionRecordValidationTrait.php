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

namespace Civi\Funding\Api4\Action\Traits;

use Civi\Api4\Utils\CoreUtil;
use Civi\Api4\Utils\FormattingUtil;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Validation\EntityValidationResult;
use Webmozart\Assert\Assert;

trait ActionRecordValidationTrait {

  use Api4Trait;

  use EntityValidatorTrait;

  /**
   * @var array<string, mixed>|null
   */
  private ?array $_defaults = NULL;

  /**
   * @var class-string<AbstractEntity<array<string, mixed>>>|null
   */
  private ?string $_entityClass = NULL;

  /**
   * @param array<string, mixed> $record
   * @param string|null $entityName
   * @param string|null $actionName
   *
   * @throws \Civi\Funding\Validation\Exception\EntityValidationFailedException
   */
  protected function formatWriteValues(&$record, $entityName = NULL, $actionName = NULL): void {
    $this->_validateRecord($record);
    // Parameters $entityName and $actionName will be added in CiviCRM 6.12:
    // https://github.com/civicrm/civicrm-core/pull/34508
    // @phpstan-ignore parameterByRef.type, arguments.count
    parent::formatWriteValues($record, $entityName, $actionName);
  }

  /**
   * @phpstan-param array<string, mixed> $record
   *
   * @throws \Civi\Funding\Validation\Exception\EntityValidationFailedException
   */
  protected function _validateRecord(array $record): void {
    // @phpstan-ignore-next-line Depending on the class it always validates to true or false.
    if (property_exists($this, 'defaults')) {
      $record += $this->defaults;
    }

    $idFieldName = $this->_getIdFieldName();
    if (!isset($record[$idFieldName])) {
      // @phpstan-ignore-next-line Depending on the class it always validates to true or false.
      if (property_exists($this, 'where')) {
        $id = WhereUtil::getInt($this->where, $idFieldName);
        if (NULL !== $id) {
          $record[$idFieldName] = $id;
        }
      }
    }

    // @phpstan-ignore-next-line Depending on the class it always validates to true or false.
    if (method_exists($this, 'matchExisting')) {
      $this->matchExisting($record);
    }

    if (isset($record[$idFieldName])) {
      $result = $this->_validateRecordUpdate($record);
    }
    else {
      $result = $this->_validateRecordCreate($record);
    }

    if (!$result->isValid()) {
      throw $result->toException();
    }
  }

  /**
   * @phpstan-param array<string, mixed> $record
   */
  protected function _validateRecordCreate(array $record): EntityValidationResult {
    $record += $this->_getDefaults();
    /*
     * Required field check copied from AbstractCreateAction::validateValues().
     * This method is called later. Though this check should happen before
     * creating the entity object.
     */
    $unmatched = $this->checkRequiredFields($record);
    if ([] !== $unmatched) {
      throw new \CRM_Core_Exception(
        "Mandatory values missing from Api4 {$this->getEntityName()}::{$this->getActionName()}: " . implode(
          ', ',
          $unmatched
        ), 'mandatory_missing', ['fields' => $unmatched]
      );
    }

    $entityClass = $this->_getEntityClass();
    $entity = $entityClass::fromArray($record);

    return $this->getEntityValidator()->validateNew($entity, $this->getCheckPermissions());
  }

  /**
   * @phpstan-param array<string, mixed> $record
   */
  protected function _validateRecordUpdate(array $record): EntityValidationResult {
    $id = $record[$this->_getIdFieldName()];
    $currentRecord = $this->getApi4()->execute($this->getEntityName(), 'get', [
      'checkPermissions' => FALSE,
      'where' => [['id', '=', $id]],
    ])->single();

    $entityClass = $this->_getEntityClass();
    $new = $entityClass::fromArray($record + $currentRecord);
    $current = $entityClass::fromArray($currentRecord);

    return $this->getEntityValidator()->validate($new, $current, $this->getCheckPermissions());
  }

  /**
   * @phpstan-return array<string, mixed>
   *   Default values for the entity.
   */
  protected function _getDefaults(): array {
    if (NULL === $this->_defaults) {
      $this->_defaults = [];
      $idFieldName = $this->_getIdFieldName();
      /**
       * @var string $name
       * @var array{data_type: string, required: bool, default_value?: mixed} $field
       */
      foreach ($this->entityFields() as $name => $field) {
        if (isset($field['default_value'])) {
          $this->_defaults[$name] = FormattingUtil::convertDataType($field['default_value'], $field['data_type']);
        }
        elseif (!$field['required'] && $name !== $idFieldName) {
          $this->_defaults[$name] = NULL;
        }
      }
    }

    return $this->_defaults;
  }

  /**
   * @phpstan-return class-string<AbstractEntity<array<string, mixed>>>
   */
  protected function _getEntityClass(): string {
    if (NULL === $this->_entityClass) {
      // @phpstan-ignore-next-line
      $namespace = substr(AbstractEntity::class, 0, strrpos(AbstractEntity::class, '\\') + 1);
      $entityClass = $namespace . $this->getEntityName() . 'Entity';
      if (!class_exists($entityClass)) {
        $entityClass = $namespace . substr($this->getEntityName(), strlen('Funding')) . 'Entity';
        Assert::classExists($entityClass);
      }
      /** @phpstan-var class-string<AbstractEntity<array<string, mixed>>> $entityClass */
      $this->_entityClass = $entityClass;
    }

    return $this->_entityClass;
  }

  protected function _getIdFieldName(): string {
    return CoreUtil::getIdFieldName($this->getEntityName());
  }

}
