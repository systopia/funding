<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\ApplicationProcess\AbstractFinancePlanItemManager;
use Civi\Funding\ClearingProcess\Form\Container\ClearableItems;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FinancePlanItemTypeInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\RemoteTools\JsonForms\Util\JsonFormsUtil;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

/**
 * @template T of \Civi\Funding\Entity\AbstractFinancePlanItemEntity
 */
abstract class AbstractClearableItemsLoader {

  /**
   * @phpstan-param AbstractFinancePlanItemManager<T> $itemManager
   */
  public function __construct(
    private readonly AbstractFinancePlanItemManager $itemManager,
    private readonly LoggerInterface $logger,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider
  ) {}

  /**
   * @phpstan-return array<string, ClearableItems<T>>
   *   Key is the property scope.
   *
   * @throws \CRM_Core_Exception
   */
  // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
  public function getClearableItems(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    JsonSchema $jsonSchema
  ): array {
    $fundingCaseTypeMetaData = $this->metaDataProvider->get($applicationProcessBundle->getFundingCaseType()->getName());

    $clearableItems = [];
    $propertySchemas = [];
    $financePlanItemSchemas = [];

    /** @phpstan-var array<T> $items */
    $items = $this->itemManager->getByApplicationProcessId(
      $applicationProcessBundle->getApplicationProcess()->getId()
    );

    foreach ($items as $item) {
      $financePlanItemType = $this->getFinancePlanItemMetaData($fundingCaseTypeMetaData, $item->getType());
      if (NULL === $financePlanItemType) {
        $this->logger->error(sprintf(
          'No finance plan item type of type "%s" in funding case type "%s" found.',
          $item->getType(),
          $applicationProcessBundle->getFundingCaseType()->getName(),
        ));

        continue;
      }

      if (!$financePlanItemType->isClearable()) {
        continue;
      }

      $path = explode('/', ltrim($item->getDataPointer(), '/'));
      // If $path is [''] then the application was last saved before
      // cost/resources items were specified in the JSON schema and validation
      // failed on update.
      if ([] !== $item->getProperties() && [''] !== $path) {
        $arrayItem = TRUE;
        $index = array_pop($path);
        Assert::integerish($index);
        $index = (int) $index;
      }
      else {
        $arrayItem = FALSE;
        $index = 0;
      }

      $scope = JsonFormsUtil::pathToScope($path);
      $propertySchema = $propertySchemas[$scope] ??= JsonSchemaUtil::getPropertySchemaAt($jsonSchema, $path);

      if (NULL === $propertySchema) {
        $this->logger->error(sprintf(
          'No property schema found for item at "%s" in JSON schema of funding case type "%s"',
          $item->getDataPointer(),
          $applicationProcessBundle->getFundingCaseType()->getName(),
        ));

        continue;
      }

      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema|null $financePlanItemSchema */
      $financePlanItemSchema = $financePlanItemSchemas[$scope] ??=
        $arrayItem
        ? $propertySchema[$this->getFinancePlanArrayItemKeyword()]
        : $propertySchema[$this->getFinancePlanNumberItemKeyword()];

      if (NULL === $financePlanItemSchema) {
        $this->logger->error(sprintf(
          'No finance plan item schema found for item at "%s" in JSON schema of funding case type "%s"',
          $item->getDataPointer(),
          $applicationProcessBundle->getFundingCaseType()->getName(),
        ));

        continue;
      }

      // phpstan-ignore voku.Coalesce
      $clearableItems[$scope] ??= new ClearableItems(
        $scope, $propertySchema, $financePlanItemSchema, $financePlanItemType
      );
      /** var non-empty-array<string, ClearableItems<T>> $clearableItems */
      $clearableItems[$scope]->items[$index] = $item;
    }

    return $clearableItems;
  }

  abstract protected function getFinancePlanArrayItemKeyword(): string;

  abstract protected function getFinancePlanItemMetaData(
    FundingCaseTypeMetaDataInterface $fundingCaseTypeMetaData,
    string $type
  ): ?FinancePlanItemTypeInterface;

  abstract protected function getFinancePlanNumberItemKeyword(): string;

}
