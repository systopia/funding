<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

class CheckAccessEvent extends AbstractApiEvent {

  protected string $action;

  protected array $values;

  private ?bool $accessGranted = NULL;

  public function getAction(): string {
    return $this->action;
  }

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
    return [
      'action',
      'values',
    ];
  }

}
