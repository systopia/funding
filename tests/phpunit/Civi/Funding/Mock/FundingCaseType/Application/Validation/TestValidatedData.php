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

namespace Civi\Funding\Mock\FundingCaseType\Application\Validation;

use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;

/**
 * @phpstan-type testValidatedDataT array<string, mixed>&array{
 *   action: string,
 *   title: string,
 *   shortDescription: string,
 *   recipient: int,
 *   startDate: string,
 *   endDate: string,
 *   amountRequested: float,
 *   comment?: array{text: string, type: string},
 * }
 */
final class TestValidatedData implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var testValidatedDataT
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
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param array<string, mixed> $validatedData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   *
   *  phpcs:enable
   */
  public function __construct(array $validatedData, array $costItemsData, array $resourcesItemsData) {
    /** @phpstan-var testValidatedDataT $validatedData */
    $this->data = $validatedData;
    $this->costItemsData = $costItemsData;
    $this->resourcesItemsData = $resourcesItemsData;
  }

  public function getAction(): string {
    return $this->data['action'];
  }

  public function getTitle(): string {
    return $this->data['title'];
  }

  public function getShortDescription(): string {
    return $this->data['shortDescription'];
  }

  public function getRecipientContactId(): int {
    return $this->data['recipient'];
  }

  public function getStartDate(): \DateTimeInterface {
    return new \DateTime($this->data['startDate']);
  }

  public function getEndDate(): \DateTimeInterface {
    return new \DateTime($this->data['endDate']);
  }

  public function getAmountRequested(): float {
    return $this->data['amountRequested'];
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
    return $this->data['comment'] ?? NULL;
  }

  public function getApplicationData(): array {
    $data = $this->data;
    unset($data['action']);
    unset($data['comment']);

    return $data;
  }

  public function getRawData(): array {
    return $this->data;
  }

}
