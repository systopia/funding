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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\UISchema;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\NonCombinedApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1StatusMarkupFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;

final class AVK1UiSchemaFactory implements NonCombinedApplicationUiSchemaFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;
  private AVK1StatusMarkupFactory $statusMarkupFactory;

  public function __construct(AVK1StatusMarkupFactory $statusMarkupFactory) {
    $this->statusMarkupFactory = $statusMarkupFactory;
  }

  public function createUiSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonFormsLayout {
    $statusMarkup = new JsonFormsMarkup($this->statusMarkupFactory->buildStatusMarkup($applicationProcessBundle));

    return new AVK1UiSchema($applicationProcessBundle->getFundingProgram()->getCurrency(), [$statusMarkup]);
  }

  public function createUiSchemaNew(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): JsonFormsLayout {
    return new AVK1UiSchema($fundingProgram->getCurrency());
  }

}
