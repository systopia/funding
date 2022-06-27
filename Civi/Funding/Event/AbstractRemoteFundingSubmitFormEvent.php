<?php
/**
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation in version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventContactIdRequiredTrait;
use Civi\RemoteTools\Event\AbstractRequestEvent;

abstract class AbstractRemoteFundingSubmitFormEvent extends AbstractRequestEvent {

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
    return array_merge($this->traitGetRequiredParams(), ['data']);
  }

}
