<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

interface ValidatedApplicationDataInterface {

  public function getAction(): string;

  public function getTitle(): string;

  public function getShortDescription(): string;

  public function getRecipientContactId(): int;

  public function getStartDate(): ?\DateTimeInterface;

  public function getEndDate(): ?\DateTimeInterface;

  public function getAmountRequested(): float;

  /**
   * @phpstan-return array<string, \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData>
   */
  public function getCostItemsData(): array;

  /**
   * @phpstan-return array<string, \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData>
   */
  public function getResourcesItemsData(): array;

  /**
   * @return array{text: string, type: string}|null
   */
  public function getComment(): ?array;

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   *   Request data without extra data like "action".
   */
  public function getApplicationData(): array;

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   *   The request data (after JSON schema validation) from which data is
   *   extracted.
   */
  public function getRawData(): array;

}
