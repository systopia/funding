<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventContactIdRequiredTrait;
use Civi\RemoteTools\Event\AbstractRequestEvent;

final class RemoteFundingCaseSubmitNewApplicationFormEvent extends AbstractRequestEvent {

  public const ACTION_CLOSE_FORM = 'closeForm';

  public const ACTION_SHOW_FORM = 'showForm';

  public const ACTION_SHOW_VALIDATION = 'showValidation';

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
   * @var string|null|self::ACTION_*
   */
  private ?string $action = NULL;

  /**
   * @var array<string, string[]>
   */
  private array $errors = [];

  /**
   * @var array{jsonSchema: array<string, mixed>, uiSchema: array<string, mixed>, data: array<string, mixed>}|null
   */
  private ?array $form = NULL;

  private ?string $message = NULL;

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

  public function getAction(): ?string {
    return $this->action;
  }

  // phpcs:disable Drupal.Commenting.FunctionComment,Squiz.WhiteSpace.FunctionSpacing
  /**
   * @param string&self::ACTION_* $action
   */
  public function setAction(string $action): self {
    $this->action = $action;

    return $this;
  }
  // phpcs:enable

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
    $this->action = self::ACTION_SHOW_VALIDATION;

    return $this;
  }

  /**
   * @return array<string, string[]>
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * @return array{jsonSchema: array<string, mixed>, uiSchema: array<string, mixed>, data: array<string, mixed>}|null
   */
  public function getForm(): ?array {
    return $this->form;
  }

  /**
   * @param array<string, mixed> $jsonSchema
   * @param array<string, mixed> $uiSchema
   * @param array<string, mixed> $data
   */
  public function setForm(array $jsonSchema, array $uiSchema, array $data = []): self {
    $this->form = [
      'jsonSchema' => $jsonSchema,
      'uiSchema' => $uiSchema,
      'data' => $data,
    ];
    $this->action = self::ACTION_SHOW_FORM;

    return $this;
  }

  public function getMessage(): ?string {
    return $this->message;
  }

  public function setMessage(string $message): self {
    $this->message = $message;

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  protected function getRequiredParams(): array {
    return array_merge($this->traitGetRequiredParams(), [
      'data',
      'fundingCaseType',
      'fundingProgram',
    ]);
  }

}
