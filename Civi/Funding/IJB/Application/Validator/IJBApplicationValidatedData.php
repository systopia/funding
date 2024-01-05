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

namespace Civi\Funding\IJB\Application\Validator;

use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;

/**
 * @phpstan-type kursValidatedDataT array<string, mixed>&array{
 *   action: string,
 *   grunddaten: array{
 *     titel: string,
 *     kurzbeschreibungDesInhalts: string,
 *     zeitraeume: non-empty-array<array{beginn: string, ende: string}>,
 *   },
 *   empfaenger: int,
 *   zuschuss: array{gesamt: float},
 *   comment?: array{text: string, type: string},
 * }
 * zeitraeume: Entries ordered ascending by "beginn".
 */
final class IJBApplicationValidatedData implements ValidatedApplicationDataInterface {

  /**
   * @phpstan-var kursValidatedDataT
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
   * @phpstan-param kursValidatedDataT $validatedData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData> $costItemsData
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData> $resourcesItemsData
   *
   * phpcs:enable
   */
  public function __construct(array $validatedData, array $costItemsData, array $resourcesItemsData) {
    $this->data = $validatedData;
    $this->costItemsData = $costItemsData;
    $this->resourcesItemsData = $resourcesItemsData;
  }

  public function getAction(): string {
    return $this->data['action'];
  }

  public function getTitle(): string {
    return $this->data['grunddaten']['titel'];
  }

  public function getShortDescription(): string {
    return $this->data['grunddaten']['kurzbeschreibungDesInhalts'];
  }

  public function getRecipientContactId(): int {
    return $this->data['empfaenger'];
  }

  public function getStartDate(): \DateTimeInterface {
    return new \DateTime($this->data['grunddaten']['zeitraeume'][0]['beginn']);
  }

  public function getEndDate(): \DateTimeInterface {
    $zeitraeume = $this->data['grunddaten']['zeitraeume'];

    return new \DateTime($zeitraeume[count($zeitraeume) - 1]['ende']);
  }

  public function getAmountRequested(): float {
    return $this->data['zuschuss']['gesamt'];
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
