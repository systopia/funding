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

namespace Civi\RemoteTools\Api4\Query;

use Civi\RemoteTools\Api4\ApiParameterInterface;

/**
 * Represents a join param in an API call.
 */
final class JoinParameter implements ApiParameterInterface {

  /**
   * @var array<Join>
   */
  private array $joins;

  public static function new(Join ...$joins): self {
    return new self(...$joins);
  }

  public function __construct(Join ...$joins) {
    $this->joins = $joins;
  }

  /**
   * @return array<Join>
   */
  public function getJoins(): array {
    return $this->joins;
  }

  /**
   * @inheritDoc
   */
  public function toParam(): ?array {
    $joins = array_map(fn (Join $join) => $join->toArray(), $this->joins);

    return [] === $joins ? NULL : $joins;
  }

}
