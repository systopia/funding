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

namespace Civi\Funding\Form;

/**
 * @codeCoverageIgnore
 */
final class FundingFormFile {

  private string $uri;

  private string $identifier;

  /**
   * @phpstan-var array<int|string, mixed>|null
   */
  private ?array $customData;

  /**
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   */
  public static function new(string $uri, string $identifier, ?array $customData): self {
    return new self($uri, $identifier, $customData);
  }

  /**
   * @phpstan-param array<int|string, mixed>|null $customData JSON serializable.
   */
  public function __construct(string $uri, string $identifier, ?array $customData) {
    $this->uri = $uri;
    $this->identifier = $identifier;
    $this->customData = $customData;
  }

  public  function getUri(): string {
    return $this->uri;
  }

  public  function getIdentifier(): string {
    return $this->identifier;
  }

  /**
   * @phpstan-return array<int|string, mixed>|null
   */
  public  function getCustomData(): ?array {
    return $this->customData;
  }

}
