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

namespace Civi\RemoteTools\Event;

use Webmozart\Assert\Assert;

class GetFieldsEvent extends AbstractRequestEvent {

  /**
   * @var array<array<string, scalar|null>>|bool
   */
  protected $loadOptions = FALSE;

  protected string $action = 'get';

  /**
   * @var array<string, null|scalar|scalar[]>
   */
  protected array $values = [];

  /**
   * @var array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   */
  private array $fields = [];

  /**
   * @return array<array<string, scalar|null>>|bool
   */
  public function getLoadOptions() {
    return $this->loadOptions;
  }

  public function getAction(): string {
    return $this->action;
  }

  /**
   * @return array<string, null|scalar|scalar[]>
   */
  public function getValues(): array {
    return $this->values;
  }

  /**
   * @param array<string, array<string, scalar>|scalar[]|scalar|null> $field
   *
   * @return $this
   */
  public function addField(array $field): self {
    Assert::string($field['name']);
    if ($this->hasField($field['name'])) {
      throw new \InvalidArgumentException(sprintf('Field "%s" already exists', $field['name']));
    }

    return $this->setField($field);
  }

  /**
   * @param string $name
   *
   * @return array<string, array<string, scalar>|scalar[]|scalar|null>
   */
  public function getField(string $name): array {
    return $this->fields[$name];
  }

  public function hasField(string $name): bool {
    return isset($this->fields[$name]);
  }

  /**
   * @param array<string, array<string, scalar>|scalar[]|scalar|null> $field
   *
   * @return $this
   */
  public function setField(array $field): self {
    Assert::string($field['name']);
    $this->fields[$field['name']] = $field;

    return $this;
  }

  /**
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   */
  public function getFields(): array {
    return $this->fields;
  }

  /**
   * @param array<string|int, array<string, array<string, scalar>|scalar[]|scalar|null>> $fields
   *
   * @return $this
   */
  public function setFields(array $fields): self {
    if (is_string(key($fields))) {
      /** @var array<string, array<string, scalar|null>> $fields */
      $this->fields = $fields;
    }
    else {
      $this->fields = [];
      foreach ($fields as $field) {
        $this->setField($field);
      }
    }

    return $this;
  }

}
