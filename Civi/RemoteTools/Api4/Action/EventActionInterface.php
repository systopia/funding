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
