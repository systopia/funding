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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidationResult;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

/**
 * This "validator" ensures that the cost items with type "bewilligt" that are
 * only available for users with permission "bsh_admin" aren't removed on form
 * submit.
 */
final class HiHApplicationFormValidator implements ApplicationFormValidatorInterface {

  use HiHSupportedFundingCaseTypesTrait;

  private ApplicationCostItemManager $costItemManager;

  public function __construct(ApplicationCostItemManager $costItemManager) {
    $this->costItemManager = $costItemManager;
  }

  public function validateExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationSchemaValidationResult $schemaValidationResult,
    bool $readOnly
  ): ApplicationFormValidationResult {
    if ($readOnly || $applicationProcessBundle->getFundingCase()->hasPermission('bsh_admin')) {
      return new ApplicationFormValidationResult(
        $schemaValidationResult->getErrorMessages(),
        $schemaValidationResult->getData(),
        $schemaValidationResult->getCostItemsData(),
        $schemaValidationResult->getResourcesItemsData(),
        $schemaValidationResult->getTaggedData(),
        $readOnly
      );
    }

    $costItemsData = $schemaValidationResult->getCostItemsData();
    $costItems = $this->costItemManager->getByApplicationProcessId(
      $applicationProcessBundle->getApplicationProcess()->getId()
    );
    foreach ($costItems as $costItem) {
      if ($costItem->getType() === 'bewilligt') {
        /**
         * @phpstan-var non-empty-string
         */
        $dataPointer = $costItem->getDataPointer();
        $costItemsData[$costItem->getIdentifier()] = new CostItemData([
          'type' => $costItem->getType(),
          'identifier' => $costItem->getIdentifier(),
          'amount' => $costItem->getAmount(),
          'properties' => $costItem->getProperties(),
          'clearing' => NULL,
          'dataPointer' => $dataPointer,
          'dataType' => 'number',
        ]);
      }
    }

    return new ApplicationFormValidationResult(
      $schemaValidationResult->getErrorMessages(),
      $schemaValidationResult->getData(),
      $costItemsData,
      $schemaValidationResult->getResourcesItemsData(),
      $schemaValidationResult->getTaggedData(),
      $readOnly
    );
  }

}
