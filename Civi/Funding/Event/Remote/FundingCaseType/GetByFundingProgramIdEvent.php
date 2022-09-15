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

namespace Civi\Funding\Event\Remote\FundingCaseType;

use Civi\Funding\Event\Remote\AbstractFundingRequestEvent;

final class GetByFundingProgramIdEvent extends AbstractFundingRequestEvent {

  /**
   * @phpstan-var array<array<string, mixed>>
   */
  private array $records = [];

  protected int $fundingProgramId;

  public function getFundingProgramId(): int {
    return $this->fundingProgramId;
  }

  /**
   * @phpstan-return array<array<string, mixed>>
   */
  public function getRecords(): array {
    return $this->records;
  }

  /**
   * @phpstan-param array<array<string, mixed>> $records
   */
  public function setRecords(array $records): self {
    $this->records = $records;

    return $this;
  }

  /**
   * @inheritDoc
   */
  protected function getRequiredParams(): array {
    return \array_merge(parent::getRequiredParams(), ['fundingProgramId']);
  }

}
