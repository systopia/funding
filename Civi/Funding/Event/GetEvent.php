<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

class GetEvent extends AbstractApiEvent {

  protected int $limit = 0;

  protected int $offset = 0;

  protected array $orderBy = [];

  protected array $select = [];

  protected array $where = [];

  private array $records = [];

  private ?int $rowCount = NULL;

  public function getWhere(): array {
    return $this->where;
  }

  public function getOrderBy(): array {
    return $this->orderBy;
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function getOffset(): int {
    return $this->offset;
  }

  public function getSelect(): array {
    return $this->select;
  }

  public function addRecord(array $record): self {
    $this->records[] = $record;

    return $this;
  }

  public function getRecords(): array {
    return $this->records;
  }

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
