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

namespace Civi\Funding\Entity;

/**
 * @phpstan-type payoutProcessT array{
 *   funding_case_id: int,
 *   status: string,
 *   amount_total: float,
 *   amount_paid_out: float,
 * }
 *
 * @phpstan-extends AbstractEntity<payoutProcessT>
 *
 * @codeCoverageIgnore
 */
final class PayoutProcessEntity extends AbstractEntity {

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  public function getAmountTotal(): float {
    return $this->values['amount_total'];
  }

  public function getAmountPaidOut(): float {
    return $this->values['amount_paid_out'];
  }

  public function setAmountPaidOut(float $amountPaidOut): self {
    $this->values['amount_paid_out'] = $amountPaidOut;

    return $this;
  }

}
