<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Api4\Action;

interface EventActionInterface {

  /**
   * @return class-string<\Civi\RemoteTools\Event\AuthorizeApiRequestEvent>
   */
  public function getAuthorizeRequestEventClass(): string;

  public function getAuthorizeRequestEventName(): string;

  /**
   * @return class-string<\Civi\RemoteTools\Event\InitApiRequestEvent>
   */
  public function getInitRequestEventClass(): string;

  public function getInitRequestEventName(): string;

  /**
   * @param string $key
   *
   * @return mixed
   */
  public function getExtraParam(string $key);

  /**
   * @return array<string, mixed>
   */
  public function getExtraParams(): array;

  public function hasExtraParam(string $key): bool;

  /**
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function setExtraParam(string $key, $value): self;

  /**
   * @param array<string, mixed> $extraParams
   *
   * @return $this
   */
  public function setExtraParams(array $extraParams): self;

  /**
   * @return string[]
   */
  public function getRequiredExtraParams(): array;

}
