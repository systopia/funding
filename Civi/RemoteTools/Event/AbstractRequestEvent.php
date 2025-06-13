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

namespace Civi\RemoteTools\Event;

use Civi\Api4\Generic\AbstractAction;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @phpstan-consistent-constructor
 */
abstract class AbstractRequestEvent extends Event {

  private string $entityName;

  private string $actionName;

  protected bool $checkPermissions = FALSE;

  protected bool $debug = FALSE;

  /**
   * @var array<string|int, mixed>
   */
  private array $debugOutput = [];

  /**
   * @param \Civi\Api4\Generic\AbstractAction $apiRequest
   * @param array<string, mixed> $extraParams
   *
   * @return static
   */
  public static function fromApiRequest(AbstractAction $apiRequest, array $extraParams = []): self {
    return new static($apiRequest->getEntityName(), $apiRequest->getActionName(),
      // Normally the remote API requester should only have permission to access
      // the remote API, so permission checks for other APIs would fail.
      ['checkPermissions' => FALSE] + $apiRequest->getParams() + $extraParams
    );
  }

  public static function getEventName(?string $entityName = NULL, ?string $actionName = NULL): string {
    $eventName = static::class;
    if (NULL !== $entityName) {
      $eventName .= '@' . $entityName;
    }

    if (NULL !== $actionName) {
      if (NULL === $entityName) {
        throw new \InvalidArgumentException('entityName is required if actionName is specified');
      }

      $eventName .= '::' . $actionName;
    }

    return $eventName;
  }

  /**
   * @param string $entityName
   * @param string $actionName
   * @param array<string, mixed> $params
   */
  public function __construct(string $entityName, string $actionName, array $params) {
    $missingParams = array_diff($this->getRequiredParams(),
      array_keys(array_filter($params, fn ($value) => NULL !== $value)));
    if ([] !== $missingParams) {
      throw new \InvalidArgumentException(sprintf('Required params missing: %s', implode(', ', $missingParams)));
    }

    $this->entityName = $entityName;
    $this->actionName = $actionName;
    $this->setParams($params);
  }

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function getActionName(): string {
    return $this->actionName;
  }

  /**
   * @return bool Defaults to FALSE.
   */
  public function isCheckPermissions(): bool {
    return $this->checkPermissions;
  }

  /**
   * @return $this
   */
  public function setCheckPermissions(bool $checkPermissions): self {
    $this->checkPermissions = $checkPermissions;

    return $this;
  }

  public function isDebug(): bool {
    return $this->debug;
  }

  /**
   * @return array<string|int, mixed>
   */
  public function getDebugOutput(): array {
    return $this->debugOutput;
  }

  /**
   * @param string $key
   * @param array<string|int, mixed> $debugOutput
   *
   * @return $this
   */
  public function addDebugOutput(string $key, array $debugOutput): self {
    $this->debugOutput[$key] = $debugOutput;

    return $this;
  }

  /**
   * @return string[]
   */
  protected function getRequiredParams(): array {
    return [];
  }

  /**
   * @param array<string, mixed|null> $params
   */
  private function setParams(array $params): void {
    foreach ($params as $key => $value) {
      if (property_exists($this, $key)) {
        $this->{$key} = $value;
      }
    }
  }

}
