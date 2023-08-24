<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Mock\RequestContext;

use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class TestRequestContext implements RequestContextInterface {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data = [];

  private int $contactId;

  private bool $remote;

  public static function newInternal(int $contactId = 1): self {
    return new static($contactId, FALSE);
  }

  public static function newRemote(int $resolvedContactId = 1): self {
    return new static($resolvedContactId, TRUE);
  }

  public function __construct(int $contactId, bool $remote) {
    $this->contactId = $contactId;
    $this->remote = $remote;
  }

  /**
   * @inheritDoc
   */
  public function get(string $key, $default = NULL) {
    return $this->data[$key] ?? $default;
  }

  /**
   * @inheritDoc
   */
  public function set(string $key, $value): void {
    $this->data[$key] = $value;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  public function setResolvedContactId(?int $contactId): void {
    Assert::notNull($contactId);
    $this->contactId = $contactId;
  }

  public function isRemote(): bool {
    return $this->remote;
  }

  public function setRemote(bool $remote): void {
    $this->remote = $remote;
  }

  public function getLoggedInContactId(): int {
    return $this->contactId;
  }

  public function getRemoteContactId(): string {
    return (string) $this->contactId;
  }

  public function setRemoteContactId(?string $remoteContactId): void {
    Assert::integerish($remoteContactId);
    $this->contactId = (int) $remoteContactId;
  }

  public function getResolvedContactId(): int {
    return $this->contactId;
  }

}
