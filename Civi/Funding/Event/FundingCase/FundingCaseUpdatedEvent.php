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

namespace Civi\Funding\Event\FundingCase;

use Civi\Funding\Entity\FundingCaseEntity;
use Symfony\Contracts\EventDispatcher\Event;

final class FundingCaseUpdatedEvent extends Event {

  private FundingCaseEntity $previousFundingCase;

  private FundingCaseEntity $fundingCase;

  public function __construct(FundingCaseEntity $previousFundingCase, FundingCaseEntity $fundingCase) {
    $this->previousFundingCase = $previousFundingCase;
    $this->fundingCase = $fundingCase;
  }

  public function getPreviousFundingCase(): FundingCaseEntity {
    return $this->previousFundingCase;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

}
