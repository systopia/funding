<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Event;

class CheckAccessEvent extends AbstractRequestEvent {

  protected string $action;

  /**
   * @var array<string, null|scalar|scalar[]>
   */
  protected array $values;

  private ?bool $accessGranted = NULL;

  public function getAction(): string {
    return $this->action;
  }

  /**
   * @return array<string, null|scalar|scalar[]>
   */
  public function getValues(): array {
    return $this->values;
  }

  public function isAccessGranted(): ?bool {
    return $this->accessGranted;
  }

  public function setAccessGranted(?bool $accessGranted): self {
    $this->accessGranted = $accessGranted;

    return $this;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'action',
      'values',
    ]);
  }

}
