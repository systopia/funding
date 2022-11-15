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

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\SonstigeAktivitaet\JsonSchema\AVK1JsonSchema;
use Civi\Funding\Form\ValidatedApplicationDataInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaString;

class AVK1JsonSchemaFactory implements ApplicationJsonSchemaFactoryInterface {

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public static function getSupportedFundingCaseTypes(): array {
    return ['AVK1SonstigeAktivitaet'];
  }

  public function __construct(
    ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->existingCaseRecipientLoader = $existingCaseRecipientLoader;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

  public function createValidatedData(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    return new AVK1ValidatedData($validationResult->getData());
  }

  public function createNewValidatedData(
    FundingCaseTypeEntity $fundingCaseType,
    ValidationResult $validationResult
  ): ValidatedApplicationDataInterface {
    return new AVK1ValidatedData($validationResult->getData());
  }

  public function createJsonSchemaExisting(
    ApplicationProcessEntity $applicationProcess,
    FundingProgramEntity $fundingProgram,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonSchema {
    $submitActions = $this->actionsDeterminer->getActions(
      $applicationProcess->getStatus(),
      $fundingCase->getPermissions()
    );
    $extraProperties = [
      'applicationProcessId' => new JsonSchemaInteger(['const' => $applicationProcess->getId(), 'readOnly' => TRUE]),
      'action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    $jsonSchema = new AVK1JsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $this->existingCaseRecipientLoader->getRecipient($fundingCase),
      $extraProperties,
      $extraKeywords,
    );

    // The readOnly keyword is not inherited, though we use it for informational purposes.
    if (!$this->actionsDeterminer->isEditAllowed($applicationProcess->getStatus(), $fundingCase->getPermissions())) {
      $jsonSchema->addKeyword('readOnly', TRUE);
    }

    return $jsonSchema;
  }

  public function createJsonSchemaInitial(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonSchema {
    $submitActions = $this->actionsDeterminer->getInitialActions($fundingProgram->getPermissions());
    $extraProperties = [
      'fundingCaseTypeId' => new JsonSchemaInteger(['const' => $fundingCaseType->getId(), 'readOnly' => TRUE]),
      'fundingProgramId' => new JsonSchemaInteger(['const' => $fundingProgram->getId(), 'readOnly' => TRUE]),
      'action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    return new AVK1JsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId),
      $extraProperties,
      $extraKeywords,
    );
  }

}
