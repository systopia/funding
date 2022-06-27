<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventContactIdRequiredTrait;
use Civi\RemoteTools\Event\AbstractRequestEvent;

final class RemoteFundingCaseValidateNewApplicationFormEvent extends AbstractRequestEvent {

  use RemoteFundingEventContactIdRequiredTrait {
    getRequiredParams as traitGetRequiredParams;
  }

  /**
   * @var array<string, mixed>
   */
  protected array $data;

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingCaseType;

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingProgram;

  /**
   * @var array<string, string[]>
   */
  private array $errors = [];

  private ?bool $valid = NULL;

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingProgram(): array {
    return $this->fundingProgram;
  }

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  public function addError(string $jsonPointer, string $message): self {
    $this->addErrorsAt($jsonPointer, [$message]);

    return $this;
  }

  /**
   * @param string $jsonPointer
   * @param non-empty-array<string> $messages
   */
  public function addErrorsAt(string $jsonPointer, array $messages): self {
    $this->errors[$jsonPointer] = array_merge($this->errors[$jsonPointer] ?? [], $messages);
    $this->valid = FALSE;

    return $this;
  }

  /**
   * @return array<string, string[]>
   */
  public function getErrors(): array {
    return $this->errors;
  }

  public function isValid(): ?bool {
    return $this->valid;
  }

  public function setValid(bool $valid): self {
    $this->valid = $valid;

    return $this;
  }

  protected function getRequiredParams(): array {
    return array_merge($this->traitGetRequiredParams(), [
      'data',
      'fundingCaseType',
      'fundingProgram',
    ]);
  }

}
