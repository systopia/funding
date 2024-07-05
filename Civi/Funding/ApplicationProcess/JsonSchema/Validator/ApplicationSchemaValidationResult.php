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

use Civi\RemoteTools\JsonSchema\Validation\ValidationResultInterface;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

/**
 * @codeCoverageIgnore
 */
final class ApplicationSchemaValidationResult {

  /**
   * @phpstan-var array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  private array $costItemsData;

  /**
   * @phpstan-var array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  private array $resourcesItemsData;

  private ValidationResultInterface $result;

  /**
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   *
   * phpcs:enable
   */
  public function __construct(ValidationResultInterface $result, array $costItemsData, array $resourcesItemsData) {
    $this->result = $result;
    $this->costItemsData = $costItemsData;
    $this->resourcesItemsData = $resourcesItemsData;
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->result->getData();
  }

  /**
   * @return array<string, non-empty-list<string>>
   */
  public function getErrorMessages(): array {
    return $this->result->getErrorMessages();
  }

  /**
   * @return array<string, non-empty-list<string>>
   */
  public function getLeafErrorMessages(): array {
    return $this->result->getLeafErrorMessages();
  }

  public function hasErrors(): bool {
    return $this->result->hasErrors();
  }

  public function isValid(): bool {
    return $this->result->isValid();
  }

  /**
   * @phpstan-return array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  public function getCostItemsData(): array {
    return $this->costItemsData;
  }

  /**
   * @phpstan-return array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  public function getResourcesItemsData(): array {
    return $this->resourcesItemsData;
  }

  public function getTaggedData(): TaggedDataContainerInterface {
    return $this->result->getTaggedData();
  }

}
