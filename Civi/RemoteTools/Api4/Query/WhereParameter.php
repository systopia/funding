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
 * Represents a where param in an API call.
 */
final class WhereParameter implements ApiParameterInterface {

  /**
   * @var array<ConditionInterface>
   */
  private array $conditions;

  /**
   * @param \Civi\RemoteTools\Api4\Query\ConditionInterface ...$conditions
   *   The conditions are linked by AND.
   *
   * @return self
   */
  public static function new(ConditionInterface ...$conditions): self {
    return new self(...$conditions);
  }

  /**
   * @param \Civi\RemoteTools\Api4\Query\ConditionInterface ...$conditions
   *   The conditions are linked by AND.
   */
  public function __construct(ConditionInterface ...$conditions) {
    $this->conditions = $conditions;
  }

  /**
   * @return array<ConditionInterface> The conditions are linked by AND.
   */
  public function getConditions(): array {
    return $this->conditions;
  }

  /**
   * @inheritDoc
   */
  public function toParam(): ?array {
    $conditions = array_map(fn (ConditionInterface $condition) => $condition->toArray(), $this->conditions);

    return [] === $conditions ? NULL : $conditions;
  }

}
