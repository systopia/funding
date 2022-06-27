<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Event;

use Civi\Api4\Utils\CoreUtil;

/**
 * @phpstan-type Comparison array{string, string, 2?:scalar}
 * Actually this should be: array{string, array<Comparison|CompositeCondition>}, so that is not possible
 * @phpstan-type CompositeCondition array{string, array<array>}
 * @phpstan-type Condition Comparison|CompositeCondition
 */
class GetEvent extends AbstractRequestEvent {

  protected int $limit = 0;

  protected int $offset = 0;

  /**
   * @var array<string, 'ASC'|'DESC'>
   */
  protected array $orderBy = [];

  /**
   * @var string[]
   */
  protected array $select = [];

  /**
   * @var array<array{string, string|mixed[], 2?: mixed}>
   */
  protected array $where = [];

  /**
   * @var array<array<string, mixed>>
   */
  private array $records = [];

  private ?int $rowCount = NULL;

  /**
   * @param string $field
   *
   * @return $this
   */
  public function addSelect(string $field): self {
    if ([] === $this->select) {
      $this->select[] = '*';
    }
    $this->select[] = $field;

    return $this;
  }

  /**
   * @param string $fieldName
   * @param string $op
   * @param mixed $value
   *
   * @return $this
   *
   * @throws \API_Exception
   */
  public function addWhere(string $fieldName, string $op, $value = NULL): self {
    if (!in_array($op, CoreUtil::getOperators(), TRUE)) {
      throw new \API_Exception('Unsupported operator');
    }

    $this->where[] = [$fieldName, $op, $value];

    return $this;
  }

  /**
   * Adds one or more AND/OR/NOT clause groups
   *
   * @param string $operator
   * @param array ...$conditions
   * @phpstan-param Condition[]|Condition ...$conditions
   *
   * @return $this
   */
  public function addClause(string $operator, array ...$conditions): self {
    $this->where[] = [$operator, $conditions];

    return $this;
  }

  /**
   * @return array<array{string, string|mixed[], 2?: mixed}>
   */
  public function getWhere(): array {
    return $this->where;
  }

  /**
   * @return array<string, 'ASC'|'DESC'>
   */
  public function getOrderBy(): array {
    return $this->orderBy;
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getOffset(): int {
    return $this->offset;
  }

  /**
   * @return string[]
   */
  public function getSelect(): array {
    return $this->select;
  }

  /**
   * @param array<string, mixed> $record
   *
   * @return $this
   */
  public function addRecord(array $record): self {
    $this->records[] = $record;

    return $this;
  }

  /**
   * @return array<array<string, mixed>>
   */
  public function getRecords(): array {
    return $this->records;
  }

  /**
   * @param array<array<string, mixed>> $records
   *
   * @return $this
   */
  public function setRecords(array $records): self {
    $this->records = $records;

    return $this;
  }

  public function setRowCount(int $rowCount): self {
    $this->rowCount = $rowCount;

    return $this;
  }

  public function getRowCount(): int {
    return $this->rowCount ?? count($this->records);
  }

}
