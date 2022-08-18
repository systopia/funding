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

namespace Civi\Funding\Event\Remote\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\AbstractFundingSubmitFormEvent;

final class SubmitFormEvent extends AbstractFundingSubmitFormEvent {

  protected ApplicationProcessEntity $applicationProcess;

  protected FundingCaseEntity $fundingCase;

  /**
   * @var array<string, mixed>
   */
  protected array $fundingCaseType;

  protected FundingProgramEntity $fundingProgram;

  public function getApplicationProcess(): ApplicationProcessEntity {
    return $this->applicationProcess;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

  /**
   * @return array<string, mixed>
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->fundingProgram;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), [
      'applicationProcess',
      'fundingCase',
      'fundingCaseType',
      'fundingProgram',
    ]);
  }

}
