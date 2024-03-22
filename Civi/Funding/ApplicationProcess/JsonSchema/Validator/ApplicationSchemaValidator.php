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

namespace Civi\Funding\ApplicationProcess\JsonSchema\Validator;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemDataCollector;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemDataCollector;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\ValidationResult;
use Civi\RemoteTools\Util\JsonConverter;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Tags\TaggedDataContainer;
use Systopia\JsonSchema\Translation\TranslatorInterface;

final class ApplicationSchemaValidator implements ApplicationSchemaValidatorInterface {

  private TranslatorInterface $translator;

  private OpisApplicationValidator $validator;

  public function __construct(TranslatorInterface $translator, OpisApplicationValidator $validator) {
    $this->translator = $translator;
    $this->validator = $validator;
  }

  /**
   * @inheritDoc
   *
   * @throws \JsonException
   */
  public function validate(
    JsonSchema $jsonSchema,
    array $data,
    int $maxErrors = 1
  ): ApplicationSchemaValidationResult {
    $validationData = JsonConverter::toStdClass($data);
    $errorCollector = new ErrorCollector();
    $costItemDataCollector = new CostItemDataCollector();
    $resourcesItemDataCollector = new ResourcesItemDataCollector();
    $taggedDataContainer = new TaggedDataContainer();

    $prevMaxErrors = $this->validator->getMaxErrors();
    try {
      $this->validator->setMaxErrors($maxErrors);
      $this->validator->validate($validationData, $jsonSchema->toStdClass(), [
        'errorCollector' => $errorCollector,
        'costItemDataCollector' => $costItemDataCollector,
        'resourcesItemDataCollector' => $resourcesItemDataCollector,
        'taggedDataContainer' => $taggedDataContainer,
      ]);
    }
    finally {
      $this->validator->setMaxErrors($prevMaxErrors);
    }

    return new ApplicationSchemaValidationResult(
      new ValidationResult(
        JsonConverter::toArray($validationData),
        $taggedDataContainer,
        $errorCollector,
        $this->translator
      ),
      $costItemDataCollector->getCostItemsData(),
      $resourcesItemDataCollector->getResourcesItemsData()
    );
  }

}
