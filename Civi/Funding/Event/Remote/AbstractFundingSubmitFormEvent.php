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

namespace Civi\Funding\Event\Remote;

use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\RemoteTools\Form\RemoteFormInterface;

abstract class AbstractFundingSubmitFormEvent extends AbstractFundingRequestEvent {

  /**
   * @var array<string, mixed>
   */
  protected array $data;

  /**
   * @phpstan-var RemoteSubmitResponseActions::*|null
   */
  private ?string $action = NULL;

  /**
   * @var array<string, string[]>
   */
  private array $errors = [];

  /**
   * @phpstan-var array<string, string>
   *   Mapping of file URIs from request to Civi download URIs.
   */
  private array $files = [];

  private ?RemoteFormInterface $form = NULL;

  private ?string $message = NULL;

  /**
   * @phpstan-return RemoteSubmitResponseActions::*|null
   */
  public function getAction(): ?string {
    return $this->action;
  }

  /**
   * @phpstan-param RemoteSubmitResponseActions::* $action
   */
  public function setAction(string $action): self {
    $this->action = $action;

    return $this;
  }

  public function addError(string $jsonPointer, string $message): self {
    $this->addErrorsAt($jsonPointer, [$message]);

    return $this;
  }

  /**
   * @param string $jsonPointer
   * @param non-empty-list<string> $messages
   */
  public function addErrorsAt(string $jsonPointer, array $messages): self {
    $this->errors[$jsonPointer] = array_merge($this->errors[$jsonPointer] ?? [], $messages);
    $this->action = RemoteSubmitResponseActions::SHOW_VALIDATION;

    return $this;
  }

  /**
   * @return array<string, string[]>
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * @phpstan-return array<string, string>
   *   Mapping of file URIs from request to Civi download URIs.
   */
  public function getFiles(): array {
    return $this->files;
  }

  /**
   * @phpstan-param array<string, string> $files
   *   Mapping of file URIs from request to Civi download URIs.
   */
  public function setFiles(array $files): self {
    $this->files = $files;

    return $this;
  }

  public function getForm(): ?RemoteFormInterface {
    return $this->form;
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
    return array_merge(parent::getRequiredParams(), ['data']);
  }

}
