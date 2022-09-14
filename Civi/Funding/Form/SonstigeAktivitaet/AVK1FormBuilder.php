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

namespace Civi\Funding\Form\SonstigeAktivitaet;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Webmozart\Assert\Assert;

final class AVK1FormBuilder {

  private ?ApplicationProcessEntity $applicationProcess = NULL;

  private ?FundingProgramEntity $fundingProgram = NULL;

  private ?FundingCaseEntity $fundingCase = NULL;

  private ?FundingCaseTypeEntity $fundingCaseType = NULL;

  private bool $isNew = FALSE;

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data = [];

  public static function new(): self {
    return new self();
  }

  public function build(): AVK1Form {
    if ($this->isNew) {
      return $this->buildNew();
    }

    return $this->buildExisting();
  }

  public function applicationProcess(ApplicationProcessEntity $applicationProcess): self {
    $this->applicationProcess = $applicationProcess;

    return $this;
  }

  /**
   * @phpstan-param array<string, mixed> $data
   */
  public function data(array $data): self {
    $this->data = $data;

    return $this;
  }

  public function fundingCase(FundingCaseEntity $fundingCase): self {
    $this->fundingCase = $fundingCase;

    return $this;
  }

  public function fundingCaseType(FundingCaseTypeEntity $fundingCaseType): self {
    $this->fundingCaseType = $fundingCaseType;

    return $this;
  }

  public function fundingProgram(FundingProgramEntity $fundingProgram): self {
    $this->fundingProgram = $fundingProgram;

    return $this;
  }

  public function isNew(bool $isNew): self {
    $this->isNew = $isNew;

    return $this;
  }

  private function buildNew(): AVK1FormNew {
    Assert::notNull($this->fundingProgram, 'fundingProgram missing');
    Assert::notNull($this->fundingCaseType, 'fundingCaseType missing');

    return new AVK1FormNew(
      $this->fundingProgram->getRequestsStartDate(),
      $this->fundingProgram->getRequestsEndDate(),
      $this->fundingProgram->getCurrency(),
      $this->fundingCaseType->getId(),
      $this->fundingProgram->getId(),
      $this->fundingProgram->getPermissions(),
      $this->data,
    );
  }

  private function buildExisting(): AVK1FormExisting {
    Assert::notNull($this->fundingProgram, 'fundingProgram missing');
    Assert::notNull($this->applicationProcess, 'applicationProcess missing');
    Assert::notNull($this->fundingCase, 'fundingCase missing');

    return new AVK1FormExisting(
      $this->fundingProgram->getRequestsStartDate(),
      $this->fundingProgram->getRequestsEndDate(),
      $this->fundingProgram->getCurrency(),
      $this->applicationProcess->getId(),
      $this->fundingCase->getPermissions(),
      $this->data,
    );
  }

}
