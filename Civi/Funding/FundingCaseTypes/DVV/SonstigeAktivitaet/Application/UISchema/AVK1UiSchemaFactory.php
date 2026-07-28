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

namespace Civi\Funding\FundingCaseTypes\DVV\SonstigeAktivitaet\Application\UISchema;

use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\DVV\SonstigeAktivitaet\Application\JsonSchema\AVK1StatusMarkupFactory;
use Civi\Funding\FundingCaseTypes\DVV\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class AVK1UiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  public function __construct(
    private readonly PossibleRecipientsLoaderInterface $possibleRecipientsLoader,
    private readonly RequestContextInterface $requestContext,
    private readonly AVK1StatusMarkupFactory $statusMarkupFactory,
  ) {}

  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    $statusMarkup = new JsonFormsMarkup($this->statusMarkupFactory->buildStatusMarkup($applicationProcessBundle));

    return new AVK1UiSchema($applicationProcessBundle->getFundingProgram()->getCurrency(), 0, [$statusMarkup]);
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    $possibleRecipients = $this->possibleRecipientsLoader->getPossibleRecipients(
      $this->requestContext->getContactId(),
      $fundingProgram
    );

    return new AVK1UiSchema(
      $fundingProgram->getCurrency(),
      1 === count($possibleRecipients) ? AVK1UiSchema::FLAG_SHOW_RECIPIENTS_CONTROL : 0
    );
  }

  public function createUiSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonFormsLayout {
    return new AVK1UiSchema($fundingProgram->getCurrency(), 0);
  }

}
