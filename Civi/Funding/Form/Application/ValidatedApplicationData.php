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

namespace Civi\Funding\Form\Application;

use Civi\Funding\Util\DateTimeUtil;
use Webmozart\Assert\Assert;

final class ValidatedApplicationData implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data;

  /**
   * @phpstan-var array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  private array $costItemsData;

  /**
   * @phpstan-var array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  private array $resourcesItemsData;

  /**
   * @phpstan-var array{
   *   title: string,
   *   short_description: string,
   *   recipient_contact_id?: int,
   *   start_date?: ?string,
   *   end_date?: ?string,
   *   amount_requested: float,
   * } May contain additional data.
   */
  private array $mappedData;

  /**
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param array<string, mixed> $validatedData
   *   Must contain the action in key '_action'.
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   * @phpstan-param array<string, mixed> $mappedData
   *   See $mappedData property for required attributes.
   *
   * phpcs:enable
   */
  public function __construct(
    array $validatedData,
    array $costItemsData,
    array $resourcesItemsData,
    array $mappedData
  ) {
    Assert::keyExists($validatedData, '_action');
    $this->data = $validatedData;
    $this->costItemsData = $costItemsData;
    $this->resourcesItemsData = $resourcesItemsData;
    // @phpstan-ignore-next-line
    $this->mappedData = $mappedData;
  }

  public function getAction(): string {
    // @phpstan-ignore-next-line
    return $this->data['_action'];
  }

  public function getTitle(): string {
    return $this->mappedData['title'];
  }

  public function getShortDescription(): string {
    return $this->mappedData['short_description'];
  }

  public function getRecipientContactId(): int {
    if (!isset($this->mappedData['recipient_contact_id'])) {
      // For existing and combined applications there doesn't need to be a
      // recipient contact ID in the mapped data. Recipients cannot be changed.
      throw new \RuntimeException('No mapped data for "recipient_contact_id"');
    }

    return $this->mappedData['recipient_contact_id'];
  }

  public function getStartDate(): ?\DateTimeInterface {
    return DateTimeUtil::toDateTimeOrNull($this->mappedData['start_date'] ?? NULL);
  }

  public function getEndDate(): ?\DateTimeInterface {
    return DateTimeUtil::toDateTimeOrNull($this->mappedData['end_date'] ?? NULL);
  }

  public function getAmountRequested(): float {
    return $this->mappedData['amount_requested'];
  }

  /**
   * @inheritDoc
   */
  public function getCostItemsData(): array {
    return $this->costItemsData;
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemsData(): array {
    return $this->resourcesItemsData;
  }

  public function getComment(): ?array {
    // @phpstan-ignore-next-line
    return $this->data['comment'] ?? NULL;
  }

  public function getMappedData(): array {
    return $this->mappedData;
  }

  public function getApplicationData(): array {
    $data = $this->data;
    unset($data['comment']);

    return array_filter($data, fn (string $key) => !str_starts_with($key, '_'), ARRAY_FILTER_USE_KEY);
  }

  public function getRawData(): array {
    return $this->data;
  }

}
