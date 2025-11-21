<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseType\MetaData;

/**
 * @phpstan-type applicationProcessStatusT array{
 *   name: string,
 *   label: string,
 *   icon?: string|null,
 *   iconColor?: string|null,
 *   clearingAvailable?: bool,
 *   eligible?: bool|null,
 *   final?: bool,
 *   inReview?: bool,
 *   inWork?: bool,
 *   rejected?: bool,
 *   snapshotRequired?: bool,
 *   withdrawn?: bool,
 * }
 *   Defaults:
 *     - icon: NULL
 *     - iconColor: NULL
 *     - clearingAvailable: eligible === TRUE
 *     - eligible: FALSE if final === TRUE, NULL otherwise
 *     - final: rejected === TRUE || withdrawn === TRUE
 *     - inReview: FALSE
 *     - inWork: FALSE
 *     - rejected: FALSE
 *     - snapshotRequired: eligible === TRUE || final === TRUE
 *     - withdrawn: FALSE
 *   The value NULL for 'eligible' means the eligibility, is not decided, yet.
 *   An application 'inWork' is in work by the applicant.
 *   A 'final' status is one in which an application process can end up, i.e.
 *   it's valid to stay in this forever.
 */
final class ApplicationProcessStatus {

  /**
   * @phpstan-var applicationProcessStatusT
   */
  private array $values;

  /**
   * @phpstan-param applicationProcessStatusT $values
   */
  public function __construct(array $values) {
    $this->values = $values;

    if ($this->isFinal() && NULL === $this->isEligible()) {
      throw new \InvalidArgumentException('The eligibility of final status has to be decided');
    }
  }

  public function getIcon(): ?string {
    return $this->values['icon'] ?? NULL;
  }

  public function getIconColor(): ?string {
    return $this->values['iconColor'] ?? NULL;
  }

  public function getName(): string {
    return $this->values['name'];
  }

  public function getLabel(): string {
    return $this->values['label'];
  }

  public function isClearingAvailable(): bool {
    return $this->values['clearingAvailable'] ?? TRUE === $this->isEligible();
  }

  public function isEligible(): ?bool {
    if (array_key_exists('eligible', $this->values)) {
      return $this->values['eligible'];
    }

    return $this->isFinal() ? FALSE : NULL;
  }

  public function isFinal(): bool {
    return $this->values['final'] ?? ($this->isRejected() || $this->isWithdrawn());
  }

  public function isInReview(): bool {
    return $this->values['inReview'] ?? FALSE;
  }

  public function isInWork(): bool {
    return $this->values['inWork'] ?? FALSE;
  }

  public function isRejected(): bool {
    return $this->values['rejected'] ?? FALSE;
  }

  public function isSnapshotRequired(): bool {
    return $this->values['snapshotRequired'] ?? (TRUE === $this->isEligible() || $this->isFinal());
  }

  public function isWithdrawn(): bool {
    return $this->values['withdrawn'] ?? FALSE;
  }

}
