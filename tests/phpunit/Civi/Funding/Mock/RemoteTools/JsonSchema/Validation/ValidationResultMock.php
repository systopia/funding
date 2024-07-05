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

namespace Civi\Funding\Mock\RemoteTools\JsonSchema\Validation;

use Civi\RemoteTools\JsonSchema\Validation\ValidationResultInterface;
use Systopia\JsonSchema\Tags\TaggedDataContainer;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

final class ValidationResultMock implements ValidationResultInterface {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data;

  /**
   * @phpstan-var array<string, non-empty-list<string>>
   */
  private array $leafErrorMessages;

  private TaggedDataContainerInterface $taggedData;

  /**
   * @phpstan-param array<string, mixed> $data
   * @phpstan-param array<string, non-empty-list<string>> $leafErrorMessages
   */
  public function __construct(
    array $data,
    array $leafErrorMessages = [],
    ?TaggedDataContainerInterface $taggedData = NULL
  ) {
    $this->data = $data;
    $this->leafErrorMessages = $leafErrorMessages;
    $this->taggedData = $taggedData ?? new TaggedDataContainer();
  }

  /**
   * @inheritDoc
   */
  public function getData(): array {
    return $this->data;
  }

  public function getTaggedData(): TaggedDataContainerInterface {
    return $this->taggedData;
  }

  /**
   * @inheritDoc
   */
  public function getErrorMessages(): array {
    return $this->leafErrorMessages;
  }

  /**
   * @inheritDoc
   */
  public function getLeafErrorMessages(): array {
    return $this->leafErrorMessages;
  }

  public function hasErrors(): bool {
    return [] !== $this->leafErrorMessages;
  }

  public function isValid(): bool {
    return [] === $this->leafErrorMessages;
  }

}
