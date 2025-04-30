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

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\PayoutProcessEntity;

/**
 * @codeCoverageIgnore
 */
trait DrawdownBundleTrait {

  protected DrawdownBundle $drawdownBundle;

  public function getDrawdownBundle(): DrawdownBundle {
    return $this->drawdownBundle;
  }

  public function getDrawdown(): DrawdownEntity {
    return $this->drawdownBundle->getDrawdown();
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->drawdownBundle->getFundingCase();
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->drawdownBundle->getFundingCaseType();
  }

  public function getFundingProgram(): FundingProgramEntity {
    return $this->drawdownBundle->getFundingProgram();
  }

  public function getPayoutProcess(): PayoutProcessEntity {
    return $this->drawdownBundle->getPayoutProcess();
  }

}
