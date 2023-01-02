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

namespace Civi\Funding\Event\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Symfony\Component\EventDispatcher\Event;

final class ApplicationProcessUpdatedEvent extends Event {

  private int $contactId;

  private ApplicationProcessEntity $applicationProcess;

  private FundingCaseEntity $fundingCase;

  private ApplicationProcessEntity $previousApplicationProcess;

  private FundingCaseTypeEntity $fundingCaseType;

  public function __construct(int $contactId,
    ApplicationProcessEntity $previousApplicationProcess,
    ApplicationProcessEntity $applicationProcess,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType
  ) {
    $this->contactId = $contactId;
    $this->previousApplicationProcess = $previousApplicationProcess;
    $this->applicationProcess = $applicationProcess;
    $this->fundingCase = $fundingCase;
    $this->fundingCaseType = $fundingCaseType;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getApplicationProcess(): ApplicationProcessEntity {
    return $this->applicationProcess;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

  public function getPreviousApplicationProcess(): ApplicationProcessEntity {
    return $this->previousApplicationProcess;
  }

  public function getFundingCaseType(): FundingCaseTypeEntity {
    return $this->fundingCaseType;
  }

}
