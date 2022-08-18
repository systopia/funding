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

use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\AbstractFundingValidateFormEvent;

final class ValidateNewApplicationFormEvent extends AbstractFundingValidateFormEvent {

  /**
   * @var array<string, mixed>&array{id: int, name: string}
   */
  protected array $fundingCaseType;

  protected FundingProgramEntity $fundingProgram;

  public function getFundingProgram(): FundingProgramEntity {
    return $this->fundingProgram;
  }

  /**
   * @return array<string, mixed>&array{id: int, name: string}
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'fundingCaseType',
      'fundingProgram',
    ]);
  }

}
