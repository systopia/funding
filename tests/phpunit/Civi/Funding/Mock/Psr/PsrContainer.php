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

namespace Civi\Funding\Mock\Psr;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class PsrContainer implements ContainerInterface {

  /**
   * @phpstan-var array<string, object>
   */
  private array $services;

  /**
   * @phpstan-param array<string, object> $services
   */
  public function __construct(array $services) {
    $this->services = $services;
  }

  /**
   * @inheritDoc
   */
  public function get($id) {
    if (!$this->has($id)) {
      throw new class(sprintf('Service with id "%s" not found', $id))
        extends \RuntimeException implements NotFoundExceptionInterface{};
    }

    return $this->services[$id];
  }

  /**
   * @inheritDoc
   */
  public function has($id): bool {
    return isset($this->services[$id]);
  }

  public function set(string $id, ?object $service): self {
    if (NULL === $service) {
      unset($this->services[$id]);
    }
    else {
      $this->services[$id] = $service;
    }

    return $this;
  }

}
