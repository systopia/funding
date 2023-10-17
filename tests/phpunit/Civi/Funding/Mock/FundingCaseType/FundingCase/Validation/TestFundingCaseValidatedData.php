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

namespace Civi\Funding\Mock\FundingCaseType\FundingCase\Validation;

use Civi\Funding\Form\FundingCase\ValidatedFundingCaseDataInterface;

/**
 * @phpstan-type testValidatedDataT array<string, mixed>&array{
 *   action: string,
 *   recipient: int,
 * }
 */
final class TestFundingCaseValidatedData implements ValidatedFundingCaseDataInterface {

  /**
   * @phpstan-var testValidatedDataT
   */
  private array $data;

  /**
   * @phpstan-param array<string, mixed> $validatedData
   */
  public function __construct(array $validatedData) {
    /** @phpstan-var testValidatedDataT $validatedData */
    $this->data = $validatedData;
  }

  public function getAction(): string {
    return $this->data['action'];
  }

  public function getRecipientContactId(): int {
    return $this->data['recipient'];
  }

  public function getFundingCaseData(): array {
    $data = $this->data;
    unset($data['action']);

    return $data;
  }

  public function getRawData(): array {
    return $this->data;
  }

}
