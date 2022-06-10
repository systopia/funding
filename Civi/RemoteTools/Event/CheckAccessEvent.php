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

  /**
   * @var array<string, mixed>
   */
  private array $requestParams = [];

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

  /**
   * @return array<string, mixed>
   */
  public function getRequestParams(): array {
    return $this->requestParams;
  }

  /**
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function setRequestParam(string $key, $value): self {
    $this->requestParams[$key] = $value;

    return $this;
  }

  /**
   * @param array<string, mixed> $requestParams
   *
   * @return $this
   */
  public function setRequestParams(array $requestParams): self {
    $this->requestParams = $requestParams;

    return $this;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'action',
      'values',
    ]);
  }

}
