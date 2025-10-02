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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema;

use Civi\Funding\Contact\FundingCaseRecipientLoaderInterface;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBApplicationJsonSchemaFactory implements NonCombinedApplicationJsonSchemaFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  private FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    FundingCaseRecipientLoaderInterface $existingCaseRecipientLoader,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
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
    $fundingCase = $applicationProcessBundle->getFundingCase();
    $fundingProgram = $applicationProcessBundle->getFundingProgram();

    return new IJBApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $this->existingCaseRecipientLoader->getRecipient($fundingCase),
    );
  }

  /**
   * @inheritDoc
   */
  public function createJsonSchemaInitial(
    int $contactId,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): JsonSchema {
    return new IJBApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      $this->possibleRecipientsLoader->getPossibleRecipients($contactId, $fundingProgram),
    );
  }

  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema {
    return new IJBApplicationJsonSchema(
      $fundingProgram->getRequestsStartDate(),
      $fundingProgram->getRequestsEndDate(),
      [],
    );
  }

}
