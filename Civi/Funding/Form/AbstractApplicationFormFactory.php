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

namespace Civi\Funding\Form;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;

abstract class AbstractApplicationFormFactory implements ApplicationFormFactoryInterface {

  public function createForm(
    ApplicationProcessEntity $applicationProcess,
    FundingProgramEntity $fundingProgram,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationFormInterface {

    return $this->doCreateFormExisting(
      $applicationProcess,
      $fundingProgram,
      $fundingCase,
      $applicationProcess->getRequestData()
    );
  }

  public function createFormOnGet(GetApplicationFormEvent $event): ApplicationFormInterface {
    return $this->createForm(
      $event->getApplicationProcess(),
      $event->getFundingProgram(),
      $event->getFundingCase(),
      $event->getFundingCaseType(),
    );
  }

  public function createFormOnSubmit(SubmitApplicationFormEvent $event): ApplicationFormInterface {
    return $this->doCreateFormExisting(
      $event->getApplicationProcess(),
      $event->getFundingProgram(),
      $event->getFundingCase(),
      $event->getData(),
    );
  }

  public function createFormOnValidate(ValidateApplicationFormEvent $event): ApplicationFormInterface {
    return $this->doCreateFormExisting(
      $event->getApplicationProcess(),
      $event->getFundingProgram(),
      $event->getFundingCase(),
      $event->getData(),
    );
  }

  public function createNewFormOnGet(GetNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->doCreateFormNew(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      []
    );
  }

  public function createNewFormOnSubmit(SubmitNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->doCreateFormNew(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      $event->getData(),
    );
  }

  public function createNewFormOnValidate(ValidateNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->doCreateFormNew(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      $event->getData(),
    );
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return in_array($fundingCaseType, get_class($this)::getSupportedFundingCaseTypes(), TRUE);
  }

  /**
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  abstract protected function doCreateFormExisting(
    ApplicationProcessEntity $applicationProcess,
    FundingProgramEntity $fundingProgram,
    FundingCaseEntity $fundingCase,
    array $data
  ): ApplicationFormInterface;

  /**
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  abstract protected function doCreateFormNew(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $data
  ): ApplicationFormInterface;

}
