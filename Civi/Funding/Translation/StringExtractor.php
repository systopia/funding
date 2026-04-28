<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Translation;

use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Application\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\ApplicationUiSchemaFactoryInterface;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;

final class StringExtractor {

  private ApplicationJsonSchemaFactoryInterface $applicationJsonSchemaFactory;

  private ApplicationUiSchemaFactoryInterface $applicationUiSchemaFactory;

  private JsonSchemaStringExtractor $jsonSchemaStringExtractor;

  private ReportFormFactoryInterface $reportFormFactory;

  private UiSchemaStringExtractor $uiSchemaStringExtractor;

  public function __construct(
    ApplicationJsonSchemaFactoryInterface $applicationJsonSchemaFactory,
    ApplicationUiSchemaFactoryInterface $applicationUiSchemaFactory,
    JsonSchemaStringExtractor $jsonSchemaStringExtractor,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
    ReportFormFactoryInterface $reportFormFactory,
    UiSchemaStringExtractor $uiSchemaStringExtractor
  ) {
    $this->applicationJsonSchemaFactory = $applicationJsonSchemaFactory;
    $this->applicationUiSchemaFactory = $applicationUiSchemaFactory;
    $this->jsonSchemaStringExtractor = $jsonSchemaStringExtractor;
    $this->reportFormFactory = $reportFormFactory;
    $this->uiSchemaStringExtractor = $uiSchemaStringExtractor;
  }

  /**
   * @return list<non-empty-string>
   */
  public function extractStrings(FundingProgramEntity $fundingProgram, FundingCaseTypeEntity $fundingCaseType): array {
    $reportForm = $this->reportFormFactory->createReportFormForTranslation($fundingProgram, $fundingCaseType);

    $strings = $this->jsonSchemaStringExtractor->extractStrings(
      $this->applicationJsonSchemaFactory->createJsonSchemaForTranslation($fundingProgram, $fundingCaseType)
    ) + $this->uiSchemaStringExtractor->extractStrings(
      $this->applicationUiSchemaFactory->createUiSchemaForTranslation($fundingProgram, $fundingCaseType)
    ) + $this->jsonSchemaStringExtractor->extractStrings($reportForm->getJsonSchema())
      + $this->uiSchemaStringExtractor->extractStrings($reportForm->getUiSchema());

    if (NULL !== $reportForm->getReceiptsAppendUiSchema()) {
      $strings += $this->uiSchemaStringExtractor->extractStrings($reportForm->getReceiptsAppendUiSchema());
    }
    if (NULL !== $reportForm->getReceiptsPrependUiSchema()) {
      $strings += $this->uiSchemaStringExtractor->extractStrings($reportForm->getReceiptsPrependUiSchema());
    }

    $metaData = $this->metaDataProvider->get($fundingCaseType->getName());

    foreach ($metaData->getCostItemTypes() as $costItemType) {
      $strings[$costItemType->getLabel()] = TRUE;
      $strings[$costItemType->getClearingLabel()] = TRUE;
      $strings[$costItemType->getPaymentPartyLabel()] = TRUE;
    }

    foreach ($metaData->getResourcesItemTypes() as $resourcesItemType) {
      $strings[$resourcesItemType->getLabel()] = TRUE;
      $strings[$resourcesItemType->getClearingLabel()] = TRUE;
      $strings[$resourcesItemType->getPaymentPartyLabel()] = TRUE;
    }

    foreach ($metaData->getApplicationProcessActions() as $action) {
      $strings[$action->getLabel()] = TRUE;
      $strings[$action->getConfirmMessage()] = TRUE;
    }

    foreach ($metaData->getFundingCaseActions() as $action) {
      $strings[$action->getLabel()] = TRUE;
      $strings[$action->getConfirmMessage()] = TRUE;
    }

    unset($strings['']);

    return array_keys($strings);
  }

}
