<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventContactIdRequiredTrait;
use Civi\RemoteTools\Event\AbstractRequestEvent;

final class RemoteFundingCaseGetNewApplicationFormEvent extends AbstractRequestEvent {

  use RemoteFundingEventContactIdRequiredTrait {
    getRequiredParams as traitGetRequiredParams;
  }

  /**
   * @var array<string, mixed>
   */
  private array $jsonSchema = [];

  /**
   * @var array<string, mixed>
   */
  private array $uiSchema = [];

  /**
   * @var array<string, mixed>
   */
  private array $data = [];

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingProgram;

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingCaseType;

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

  /**
   * @return array<string, mixed>
   */
  public function getJsonSchema(): array {
    return $this->jsonSchema;
  }

  /**
   * @param array<string, mixed> $jsonSchema
   */
  public function setJsonSchema(array $jsonSchema): self {
    $this->jsonSchema = $jsonSchema;

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getUiSchema(): array {
    return $this->uiSchema;
  }

  /**
   * @param array<string, mixed> $uiSchema
   */
  public function setUiSchema(array $uiSchema): self {
    $this->uiSchema = $uiSchema;

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @param array<string, mixed> $data
   */
  public function setData(array $data): self {
    $this->data = $data;

    return $this;
  }

  protected function getRequiredParams(): array {
    return array_merge($this->traitGetRequiredParams(), [
      'fundingProgram',
      'fundingCaseType',
    ]);
  }

}
