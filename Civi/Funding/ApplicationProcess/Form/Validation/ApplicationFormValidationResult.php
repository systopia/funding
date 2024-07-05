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

namespace Civi\Funding\ApplicationProcess\Form\Validation;

use Civi\Funding\Form\Application\ValidatedApplicationData;
use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;
use Civi\Funding\Form\Application\ValidatedApplicationDataInvalid;
use Civi\Funding\Form\MappedData\MappedDataLoader;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

/**
 * @codeCoverageIgnore
 */
final class ApplicationFormValidationResult {

  /**
   * @phpstan-var array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  private array $costItemsData;

  /**
   * @phpstan-var array<string, non-empty-list<string>>
   */
  private array $errorMessages;

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data;

  /**
   * @phpstan-var array<string, mixed>
   *
   * Must match the following type for valid results: array{
   *   title: string,
   *   short_description: string,
   *   recipient_contact_id?: int,
   *   start_date?: ?string,
   *   end_date?: ?string,
   *   amount_requested: float,
   * } May contain additional data.
   *
   * recipient_contact_id is required when a funding case is created, as well.
   */
  private array $mappedData;

  private bool $readOnly;

  /**
   * @phpstan-var array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  private array $resourcesItemsData;

  private TaggedDataContainerInterface $taggedData;

  /**
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param array<string, non-empty-list<string>> $errorMessages
   * @phpstan-param array<string, mixed> $data
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   *
   * phpcs:enable
   */
  public function __construct(
    array $errorMessages,
    array $data,
    array $costItemsData,
    array $resourcesItemsData,
    TaggedDataContainerInterface $taggedData,
    bool $readOnly
  ) {
    $this->errorMessages = $errorMessages;
    $this->data = $data;
    $this->costItemsData = $costItemsData;
    $this->resourcesItemsData = $resourcesItemsData;
    $this->taggedData = $taggedData;
    $mappedDataLoader = new MappedDataLoader();
    $this->mappedData = $mappedDataLoader->getMappedData($taggedData);
    $this->readOnly = $readOnly;
  }

  /**
   * @return string
   *   The submitted action. Can be an empty string on invalid submissions.
   */
  public function getAction(): string {
    $action = $this->data['_action'] ?? NULL;

    return is_string($action) ? $action : '';
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

  /**
   * @phpstan-return array<string, non-empty-list<string>>
   *   JSON pointers mapped to error messages.
   */
  public function getErrorMessages(): array {
    return $this->errorMessages;
  }

  public function hasErrors(): bool {
    return [] !== $this->errorMessages;
  }

  public function isValid(): bool {
    return [] === $this->errorMessages;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  public function getTaggedData(): TaggedDataContainerInterface {
    return $this->taggedData;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getMappedData(): array {
    return $this->mappedData;
  }

  /**
   * @param mixed $value
   */
  public function setMappedValue(string $fieldName, $value): self {
    $this->mappedData[$fieldName] = $value;

    return $this;
  }

  public function isReadOnly(): bool {
    return $this->readOnly;
  }

  public function setReadOnly(bool $readOnly): self {
    $this->readOnly = $readOnly;

    return $this;
  }

  public function getValidatedData(): ValidatedApplicationDataInterface {
    if (!$this->isValid()) {
      return new ValidatedApplicationDataInvalid($this->getData());
    }

    return new ValidatedApplicationData(
      $this->getData(),
      $this->getCostItemsData(),
      $this->getResourcesItemsData(),
      $this->getMappedData()
    );
  }

}
