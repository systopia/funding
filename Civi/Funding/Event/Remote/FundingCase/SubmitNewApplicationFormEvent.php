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

namespace Civi\Funding\Event\Remote\FundingCase;

use Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent;

final class SubmitNewApplicationFormEvent extends AbstractFundingSubmitFormEvent {

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingCaseType;

  /**
   * @var array<string, mixed>&array{id: int}
   */
  protected array $fundingProgram;

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  /**
   * @return array<string, mixed>&array{id: int}
   */
  public function getFundingProgram(): array {
    return $this->fundingProgram;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'fundingCaseType',
      'fundingProgram',
    ]);
  }

}
