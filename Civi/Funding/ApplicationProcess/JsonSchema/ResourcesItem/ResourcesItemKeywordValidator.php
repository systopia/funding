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

namespace Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem;

use CRM_Funding_ExtensionUtil as E;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\KeywordValidators\AbstractKeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Errors\ErrorCollectorUtil;
use Webmozart\Assert\Assert;

final class ResourcesItemKeywordValidator extends AbstractKeywordValidator {

  use ErrorTrait;

  private NumberResourcesItemDataFactory $resourcesItemDataFactory;

  public function __construct(NumberResourcesItemDataFactory $resourcesItemDataFactory) {
    $this->resourcesItemDataFactory = $resourcesItemDataFactory;
  }

  /**
   * @inheritDoc
   */
  public function validate(ValidationContext $context): ?ValidationError {
    // Let all validations and calculations be handled first.
    if (NULL !== $this->next) {
      $error = $this->next->validate($context);
      if (NULL !== $error) {
        return $error;
      }
    }

    if (ErrorCollectorUtil::getErrorCollector($context)->hasErrors()) {
      return NULL;
    }

    if ('null' === $context->currentDataType()) {
      return NULL;
    }

    if (in_array($context->currentDataType(), ['number', 'integer'], TRUE)) {
      return $this->collectResourcesItemData($context);
    }

    if ('array' !== $context->currentDataType()) {
      throw new \RuntimeException(sprintf('Expected data type array, got "%s"', $context->currentDataType()));
    }

    // @phpstan-ignore-next-line
    $count = count($context->currentData());
    for ($i = 0; $i < $count; ++$i) {
      try {
        $context->pushDataPath($i);
        $error = $this->collectResourcesItemData($context);
        if (NULL !== $error) {
          return $error;
        }
      }
      finally {
        $context->popDataPath();
      }
    }

    return NULL;
  }

  private function collectResourcesItemData(ValidationContext $context): ?ValidationError {
    try {
      $resourcesItemData = $this->resourcesItemDataFactory->createResourcesItemData($context);
      if (NULL === $resourcesItemData || $resourcesItemData->getAmount() === 0.0) {
        return NULL;
      }

      $resourcesItemDataCollector = ResourcesItemDataCollectorUtil::getResourcesItemCollector($context);
      if ($resourcesItemDataCollector->hasIdentifier($resourcesItemData->getIdentifier())) {
        $schema = $context->schema();
        Assert::notNull($schema);

        return $this->error(
          $schema,
          $context,
          '$resourcesItem',
          E::ts('Duplicate resources item identifier "{identifier}"'),
          ['identifier' => $resourcesItemData->getIdentifier()]
        );
      }

      $resourcesItemDataCollector->addResourcesItemData($resourcesItemData);

      return NULL;
    }
    catch (\InvalidArgumentException $e) {
      // If this happens, there must be an error in the schema because there
      // were no violation errors nevertheless a variable could not be resolved.
      $schema = $context->schema();
      Assert::notNull($schema);

      return $this->error($schema, $context, '$resourcesItem', $e->getMessage());
    }
  }

}
