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

use Civi\Api4\Utils\CoreUtil;

/**
 * @property array<array{string, string|mixed[], 2?: mixed, 3?: bool}> $where
 * @method array<array{string, string|mixed[], 2?: mixed, 3?: bool}> getWhere()
 *
 * @phpstan-type Comparison array{string, string, 2?:scalar}
 * Actually this should be: array{string, array<Comparison|CompositeCondition>}, so that is not possible
 * @phpstan-type CompositeCondition array{string, array<array>}
 * @phpstan-type Condition Comparison|CompositeCondition
 * Actually this should be array{string, string, ...Condition}, so this is not possible
 * @phpstan-type Join array<string|Condition>
 */
class DAOGetEvent extends GetEvent {

  /**
   * @var string[]
   */
  protected array $groupBy = [];

  /**
   * @var array<Join>
   */
  protected array $join = [];

  /**
   * @var array<array{string, string, mixed}>
   */
  protected array $having = [];

  /**
   * @param string $fieldName
   * @param string $op
   * @param mixed $value
   * @param bool $isExpression
   *
   * @return $this
   *
   * @throws \API_Exception
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function addWhere(string $fieldName, string $op, $value = NULL, bool $isExpression = FALSE): GetEvent {
    if (!in_array($op, CoreUtil::getOperators(), TRUE)) {
      throw new \API_Exception('Unsupported operator');
    }
    $this->where[] = [$fieldName, $op, $value, $isExpression];

    return $this;
  }

  /**
   * @return string[]
   */
  public function getGroupBy(): array {
    return $this->groupBy;
  }

  /**
   * @param string $field
   *
   * @return $this
   */
  public function addGroupBy(string $field): self {
    $this->groupBy[] = $field;

    return $this;
  }

  /**
   * @param string $expr
   * @param string $op
   * @param mixed $value
   *
   * @return $this
   *
   * @throws \API_Exception
   */
  public function addHaving(string $expr, string $op, $value = NULL): self {
    if (!in_array($op, CoreUtil::getOperators(), TRUE)) {
      throw new \API_Exception('Unsupported operator');
    }
    $this->having[] = [$expr, $op, $value];

    return $this;
  }

  /**
   * @return array<array{string, string, mixed}>
   */
  public function getHaving(): array {
    return $this->having;
  }

  /**
   * @param string $entity
   * @param string $type
   * @param null|string $bridge
   * @param array ...$conditions
   * @phpstan-param Condition ...$conditions
   *
   * @return $this
   */
  public function addJoin(string $entity, string $type = 'LEFT', ?string $bridge = NULL, array ...$conditions): self {
    if (NULL !== $bridge) {
      array_unshift($conditions, $bridge);
    }
    array_unshift($conditions, $entity, $type);
    $this->join[] = $conditions;

    return $this;
  }

  /**
   * @return array<Join>
   */
  public function getJoin(): array {
    return $this->join;
  }

}
