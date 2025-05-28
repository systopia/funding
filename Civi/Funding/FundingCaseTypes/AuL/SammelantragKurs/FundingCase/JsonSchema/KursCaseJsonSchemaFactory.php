<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\FundingCase\JsonSchema;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\FundingCase\FundingCaseJsonSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\FundingCase\Actions\KursCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class KursCaseJsonSchemaFactory implements FundingCaseJsonSchemaFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  private ApplicationProcessManager $applicationProcessManager;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  private KursCaseActionsDeterminer $actionsDeterminer;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader,
    KursCaseActionsDeterminer $actionsDeterminer
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
    $this->actionsDeterminer = $actionsDeterminer;
  }

  public function createJsonSchemaUpdate(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ): JsonSchema {
    $applicationProcesses = $this->applicationProcessManager->getByFundingCaseId($fundingCase->getId());

    $submitActions = $this->actionsDeterminer->getActions(
      $fundingCase->getStatus(),
      array_map(
        fn (ApplicationProcessEntity $applicationProcess) => $applicationProcess->getFullStatus(),
        $applicationProcesses
      ),
      $fundingCase->getPermissions()
    );
    if ([] === $submitActions) {
      // Enums must not be empty.
      $submitActions = [NULL];
    }

    return new JsonSchemaObject(
      ['_action' => new JsonSchemaString(['enum' => $submitActions])],
      ['required' => ['_action']],
    );
  }

  public function createJsonSchemaNew(
    int $contactId,
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonSchema {
    $submitActions = $this->actionsDeterminer->getInitialActions($fundingProgram->getPermissions());
    if ([] === $submitActions) {
      // Enums must not be empty.
      $submitActions = [NULL];
    }

    $extraProperties = [
      '_action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    return new KursNewCaseJsonSchema(
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId, $fundingProgram),
      $extraProperties,
      $extraKeywords,
    );
  }

}
