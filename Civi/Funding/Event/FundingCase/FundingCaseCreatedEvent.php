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
use Symfony\Component\EventDispatcher\Event;

/**
 * @phpstan-type fundingProgramT array<string, mixed>&array{id: int, permissions: array<int, string>}
 */
final class FundingCaseCreatedEvent extends Event {

  private int $contactId;

  private FundingCaseEntity $fundingCase;

  /**
   * @var array
   * @phpstan-var array<string, mixed>&array{id: int}
   */
  private array $fundingCaseType;

  /**
   * @var array
   * @phpstan-var fundingProgramT
   */
  private array $fundingProgram;

  /**
   * @phpstan-param fundingProgramT $fundingProgram
   * @phpstan-param array<string, mixed>&array{id: int} $fundingCaseType
   */
  public function __construct(int $contactId, FundingCaseEntity $fundingCase,
    array $fundingProgram, array $fundingCaseType
  ) {
    $this->contactId = $contactId;
    $this->fundingCase = $fundingCase;
    $this->fundingProgram = $fundingProgram;
    $this->fundingCaseType = $fundingCaseType;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getFundingCase(): FundingCaseEntity {
    return $this->fundingCase;
  }

  /**
   * @phpstan-return array<string, mixed>&array{id: int}
   */
  public function getFundingCaseType(): array {
    return $this->fundingCaseType;
  }

  /**
   * @phpstan-return fundingProgramT
   */
  public function getFundingProgram(): array {
    return $this->fundingProgram;
  }

}
