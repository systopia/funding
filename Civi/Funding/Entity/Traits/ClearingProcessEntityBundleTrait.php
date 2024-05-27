<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Entity\Traits;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

/**
 * @codeCoverageIgnore
 */
trait ClearingProcessEntityBundleTrait {

  protected ClearingProcessEntityBundle $clearingProcessBundle;

  public function getApplicationProcess(): ApplicationProcessEntity {
    return $this->clearingProcessBundle->getApplicationProcess();
  }

  public function getApplicationProcessBundle(): ApplicationProcessEntityBundle {
    return $this->clearingProcessBundle->getApplicationProcessBundle();
  }

  public function getClearingProcessBundle(): ClearingProcessEntityBundle {
    return $this->clearingProcessBundle;
  }

  public function getClearingProcess(): ClearingProcessEntity {
    return $this->clearingProcessBundle->getClearingProcess();
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->clearingProcessBundle->getFundingCase();
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->clearingProcessBundle->getFundingCaseType();
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->clearingProcessBundle->getFundingProgram();
  }

}
