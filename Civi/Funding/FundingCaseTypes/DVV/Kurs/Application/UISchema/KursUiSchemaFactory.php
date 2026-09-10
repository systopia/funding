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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema;

use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema\KursStatusMarkupFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Traits\KursSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class KursUiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  public function __construct(
    private readonly PossibleRecipientsLoaderInterface $possibleRecipientsLoader,
    private readonly RequestContextInterface $requestContext,
    private readonly KursStatusMarkupFactory $statusMarkupFactory,
  ) {}

  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    $statusMarkup = new JsonFormsMarkup($this->statusMarkupFactory->buildStatusMarkup($applicationProcessBundle));

    return new KursUiSchema($applicationProcessBundle->getFundingProgram()->getCurrency(), 0, [$statusMarkup]);
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    $possibleRecipients = $this->possibleRecipientsLoader->getPossibleRecipients(
      $this->requestContext->getContactId(),
      $fundingProgram
    );

    return new KursUiSchema(
      $fundingProgram->getCurrency(),
      1 === count($possibleRecipients) ? KursUiSchema::FLAG_SHOW_RECIPIENTS_CONTROL : 0
    );
  }

  public function createUiSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonFormsLayout {
    return new KursUiSchema($fundingProgram->getCurrency(), 0);
  }

}
