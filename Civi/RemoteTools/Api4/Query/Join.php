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

use Webmozart\Assert\Assert;

/**
 * @phpstan-type ComparisonT array{string, string, 2?: scalar|array<scalar>}
 * Actually this should be: array{string, array<int, ComparisonT|CompositeConditionT>}, so that is not possible
 * @phpstan-type CompositeConditionT array{string, array<int, array>}
 * @phpstan-type ConditionT ComparisonT|CompositeConditionT
 */
final class Join {

  private string $entityName;

  private string $alias;

  private string $type;

  private ?string $bridge;

  private ?ConditionInterface $condition;

  public static function new(string $entityName, string $alias, string $type, ConditionInterface $condition): self {
    return new self($entityName, $alias, $type, NULL, $condition);
  }

  public static function newWithBridge(string $entityName, string $alias, string $type, string $bridge,
    ?ConditionInterface $condition = NULL
  ): self {
    return new self($entityName, $alias, $type, $bridge, $condition);
  }

  public function __construct(string $entityName, string $alias, string $type, ?string $bridge,
    ?ConditionInterface $condition
  ) {
    Assert::notSame([$bridge, $condition], [NULL, NULL], 'At least bridge or condition must not be NULL');

    $this->entityName = $entityName;
    $this->alias = $alias;
    $this->type = $type;
    $this->bridge = $bridge;
    $this->condition = $condition;
  }

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function getAlias(): string {
    return $this->alias;
  }

  public function getType(): string {
    return $this->type;
  }

  public function getBridge(): ?string {
    return $this->bridge;
  }

  public function getCondition(): ?ConditionInterface {
    return $this->condition;
  }

  /**
   * Actually this should be array{string, string, string|ConditionT, ...ConditionT}, so this is not possible
   * @phpstan-return array<int, string|ConditionT>
   *
   * @return array
   */
  public function toArray(): array {
    $join = [$this->entityName . ' AS ' . $this->alias, $this->type];
    if (NULL !== $this->bridge) {
      $join[] = $this->bridge;
    }
    if (NULL !== $this->condition) {
      $join[] = $this->condition->toArray();
    }

    return $join;
  }

}
