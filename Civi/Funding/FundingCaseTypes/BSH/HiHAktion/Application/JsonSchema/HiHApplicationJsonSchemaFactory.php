<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema;

use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class HiHApplicationJsonSchemaFactory implements NonCombinedApplicationJsonSchemaFactoryInterface {

  use HiHSupportedFundingCaseTypesTrait;

  use HasReviewPermissionTrait;

  private HiHApplicationActionsDeterminer $actionsDeterminer;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    HiHApplicationActionsDeterminer $actionsDeterminer,
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->existingCaseRecipientLoader = $existingCaseRecipientLoader;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

    $submitActions = $this->actionsDeterminer->getActions(
      $applicationProcess->getFullStatus(),
      $applicationProcessStatusList,
      $fundingCase->getPermissions()
    );
    if ([] === $submitActions) {
      // empty array is not allowed as enum
      $submitActions = [NULL];
    }
    $extraProperties = [
      '_action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    if ($this->hasReviewPermission($fundingCase->getPermissions())) {
      $extraProperties['comment'] = new JsonSchemaComment();
    }
    else {
      // Prevent adding a comment without permission
      $extraProperties['comment'] = new JsonSchemaNull();
    }

    $jsonSchema = new HiHApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $this->existingCaseRecipientLoader->getRecipient($fundingCase),
      $extraProperties,
      $extraKeywords,
    );

    // The readOnly keyword is not inherited, though we use it for informational purposes.
    if (!$this->actionsDeterminer->isEditAllowed(
      $applicationProcess->getFullStatus(),
      $applicationProcessStatusList,
      $fundingCase->getPermissions()
    )) {
      $jsonSchema->addKeyword('readOnly', TRUE);
    }

    return $jsonSchema;
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaInitial(
    int $contactId,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    $submitActions = $this->actionsDeterminer->getInitialActions($fundingProgram->getPermissions());
    $extraProperties = [
      '_action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    return new HiHApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId, $fundingProgram),
      $extraProperties,
      $extraKeywords,
    );
  }

}
