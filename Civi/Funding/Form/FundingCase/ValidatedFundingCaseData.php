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

namespace Civi\Funding\Form\FundingCase;

use Civi\Funding\Form\MappedData\MappedDataLoader;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;
use Webmozart\Assert\Assert;

final class ValidatedFundingCaseData implements ValidatedFundingCaseDataInterface {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data;

  /**
   * @phpstan-var array{
   *   recipient_contact_id: int
   * } May contain additional data.
   */
  private array $mappedData;

  /**
   * @phpstan-param array<string, mixed> $validatedData
   *   Must contain the action in key '_action'.
   */
  public function __construct(
    array $validatedData,
    TaggedDataContainerInterface $taggedData
  ) {
    Assert::keyExists($validatedData, '_action');
    $this->data = $validatedData;
    $mappedDataLoader = new MappedDataLoader();
    // @phpstan-ignore-next-line
    $this->mappedData = $mappedDataLoader->getMappedData($taggedData);
  }

  public function getAction(): string {
    // @phpstan-ignore-next-line
    return $this->data['_action'];
  }

  public function getRecipientContactId(): int {
    return $this->mappedData['recipient_contact_id'];
  }

  /**
   * @inheritDoc
   */
  public function getFundingCaseData(): array {
    return array_filter($this->data, fn (string $key) => !str_starts_with($key, '_'), ARRAY_FILTER_USE_KEY);
  }

  /**
   * @inheritDoc
   */
  public function getRawData(): array {
    return $this->data;
  }

}
