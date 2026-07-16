<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\DVV\Personalkosten\Application\UiSchema;

use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\DVV\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class PersonalkostenApplicationUiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  public function __construct(
    private readonly RequestContextInterface $requestContext,
    private readonly PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {}

  /**
   * @inheritDoc
   */
  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    return new PersonalkostenApplicationUiSchema($applicationProcessBundle->getFundingProgram()->getCurrency(), 0);
  }

  /**
   * @inheritDoc
   */
  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    $possibleRecipients = $this->possibleRecipientsLoader->getPossibleRecipients(
      $this->requestContext->getContactId(),
      $fundingProgram
    );

    return new PersonalkostenApplicationUiSchema(
      $fundingProgram->getCurrency(),
      1 === count($possibleRecipients) ? 0 : PersonalkostenApplicationUiSchema::FLAG_SHOW_RECIPIENTS_CONTROL
    );
  }

  /**
   * @inheritDoc
   */
  public function createUiSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonFormsLayout {
    return new PersonalkostenApplicationUiSchema(
      $fundingProgram->getCurrency(), PersonalkostenApplicationUiSchema::FLAG_SHOW_RECIPIENTS_CONTROL
    );
  }

}
