<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseType\MetaData;

use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type resorcesItemT array{
 *    name: non-empty-string,
 *    label: non-empty-string,
 *    clearable: bool,
 *    clearingLabel?: non-empty-string|null,
 *    paymentPartyLabel?: non-empty-string|null,
 *  }
 *    Defaults:
 *      - clearingLabel: The value of label
 *      - paymentPartyLabel: E::ts('Payer')
 */
final class ResourcesItemType implements ResourcesItemTypeInterface {

  /**
   * @phpstan-param resorcesItemT $values
   */
  public function __construct(
    private readonly array $values
  ) {}

  public function getName(): string {
    return $this->values['name'];
  }

  public function getLabel(): string {
    return $this->values['label'];
  }

  public function isClearable(): bool {
    return $this->values['clearable'];
  }

  public function getClearingLabel(): string {
    return $this->values['clearingLabel'] ?? $this->getLabel();
  }

  public function getPaymentPartyLabel(): string {
    /** @var non-empty-string */
    return $this->values['paymentPartyLabel'] ?? E::ts('Payer');
  }

}
