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

namespace Civi\Funding\SammelantragKurs\FundingCase\Validation;

use Civi\Funding\Form\FundingCase\ValidatedFundingCaseDataInterface;

/**
 * @phpstan-type kursValidatedDataT array{
 *   _action: string,
 *   empfaenger: int,
 * }
 */
final class KursCaseValidatedData implements ValidatedFundingCaseDataInterface {

  /**
   * @phpstan-var kursValidatedDataT
   */
  private array $data;

  /**
   * @phpstan-param kursValidatedDataT $data
   */
  public function __construct(array $data) {
    $this->data = $data;
  }

  public function getAction(): string {
    return $this->data['_action'];
  }

  public function getRecipientContactId(): int {
    return $this->data['empfaenger'];
  }

  /**
   * @inheritDoc
   */
  public function getFundingCaseData(): array {
    $data = $this->data;
    unset($data['_action']);

    return $data;
  }

  /**
   * @inheritDoc
   */
  public function getRawData(): array {
    return $this->data;
  }

}
