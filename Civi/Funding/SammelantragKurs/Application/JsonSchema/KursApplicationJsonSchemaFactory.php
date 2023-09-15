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

namespace Civi\Funding\SammelantragKurs\Application\JsonSchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\Funding\Form\CombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationActionsDeterminer;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class KursApplicationJsonSchemaFactory implements CombinedApplicationJsonSchemaFactoryInterface {

  use HasReviewPermissionTrait;

  use KursSupportedFundingCaseTypesTrait;

  private KursApplicationActionsDeterminer $actionsDeterminer;

  public function __construct(
    KursApplicationActionsDeterminer $actionsDeterminer
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
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
      'action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    if ($this->hasReviewPermission($fundingCase->getPermissions())) {
      $extraProperties['comment'] = new JsonSchemaComment();
    }
    else {
      // Prevent adding a comment without permission
      $extraProperties['comment'] = new JsonSchemaNull();
    }

    $jsonSchema = new KursApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
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
  public function createJsonSchemaAdd(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
    FundingCaseEntity $fundingCase
  ): JsonSchema {
    return $this->createSchema($fundingProgram);
  }

  private function createSchema(FundingProgramEntity $fundingProgram): KursApplicationJsonSchema {
    $submitActions = $this->actionsDeterminer->getInitialActions($fundingProgram->getPermissions());
    $extraProperties = [
      'action' => new JsonSchemaString(['enum' => $submitActions]),
    ];
    $extraKeywords = ['required' => array_keys($extraProperties)];

    return new KursApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $extraProperties,
      $extraKeywords,
    );
  }

}
