<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Event;

use Civi\Api4\Utils\CoreUtil;

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
   * @param string $fieldName
   * @param string $op
   * @param mixed $value
   *
   * @return $this
   *
   * @throws \API_Exception
   */
  public function addWhere(string $fieldName, string $op, $value = NULL): self {
    if (!in_array($op, CoreUtil::getOperators())) {
      throw new \API_Exception('Unsupported operator');
    }

    $this->where[] = [$fieldName, $op, $value];

    return $this;
  }

  /**
   * Adds one or more AND/OR/NOT clause groups
   *
   * @param string $operator
   * @param mixed $condition1 ... $conditionN
   *   Either a nested array of arguments, or a variable number of arguments passed to this function.
   *
   * @return $this
   */
  public function addClause(string $operator, $condition1): self {
    if (!is_array($condition1) || !is_array($condition1[0])) {
      $condition1 = array_slice(func_get_args(), 1);
    }
    $this->where[] = [$operator, $condition1];

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
