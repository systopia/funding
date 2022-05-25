<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

class GetFieldsEvent extends AbstractApiEvent {

  /**
   * @var array|bool
   */
  protected $loadOptions = FALSE;

  protected string $action = 'get';

  protected array $values = [];

  private array $fields = [];

  public function getLoadOptions() {
    return $this->loadOptions;
  }

  public function getAction(): string {
    return $this->action;
  }

  public function getValues(): array {
    return $this->values;
  }

  public function addField(array $field): self {
    if ($this->hasField($field['name'])) {
      throw new \InvalidArgumentException(sprintf('Field "%s" already exists', $field['name']));
    }

    return $this->setField($field);
  }

  public function getField(string $name): array {
    return $this->fields[$name];
  }

  public function hasField(string $name): bool {
    return isset($this->fields[$name]);
  }

  public function setField(array $field): self {
    $this->fields[$field['name']] = $field;

    return $this;
  }

  public function getFields(): array {
    return $this->fields;
  }

  public function setFields(array $fields): self {
    if (is_string(key($fields))) {
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
