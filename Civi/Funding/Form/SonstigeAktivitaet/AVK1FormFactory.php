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

use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
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
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\ApplicationFormInterface;
use Civi\Funding\Form\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\ValidatedApplicationDataInterface;
use Civi\Funding\Form\Validation\ValidationResult;

class AVK1FormFactory implements ApplicationFormFactoryInterface {

  private ApplicationSubmitActionsFactoryInterface $submitActionsFactory;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public static function getSupportedFundingCaseType(): string {
    return 'AVK1SonstigeAktivitaet';
  }

  public function __construct(
    ApplicationSubmitActionsFactoryInterface $submitActionsFactory,
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->submitActionsFactory = $submitActionsFactory;
    $this->existingCaseRecipientLoader = $existingCaseRecipientLoader;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

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

  public function createValidatedData(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    return new AVK1ValidatedData($validationResult->getData());
  }

  public function createNewFormOnGet(GetNewApplicationFormEvent $event): ApplicationFormInterface {
    return $this->doCreateFormNew(
      $event->getContactId(),
      $event->getFundingProgram(),
      $event->getFundingCaseType(),
      [],
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

  public function createNewValidatedData(
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    return new AVK1ValidatedData($validationResult->getData());
  }

  /**
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  private function doCreateFormExisting(
    ApplicationProcessEntity $applicationProcess,
    FundingProgramEntity $fundingProgram,
    FundingCaseEntity $fundingCase,
    array $data
  ): AVK1FormExisting {

    return new AVK1FormExisting(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $fundingProgram->getCurrency(),
      $applicationProcess->getId(),
      $this->existingCaseRecipientLoader->getRecipient($fundingCase),
      $this->submitActionsFactory->createSubmitActions(
        $applicationProcess->getStatus(), $fundingCase->getPermissions()
      ),
      !$this->submitActionsFactory->isEditAllowed(
        $applicationProcess->getStatus(), $fundingCase->getPermissions()
      ),
      $data,
    );
  }

  /**
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  private function doCreateFormNew(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    array $data
  ): AVK1FormNew {
    return new AVK1FormNew(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $fundingProgram->getCurrency(),
      $fundingCaseType->getId(),
      $fundingProgram->getId(),
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId),
      $this->submitActionsFactory->createSubmitActionsForNew($fundingProgram->getPermissions()),
      $data
    );
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return static::getSupportedFundingCaseType() === $fundingCaseType;
  }

}
