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

namespace Civi\Funding\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractGetOptionsEvent extends Event {

  /**
   * @phpstan-var array<string, string>
   */
  private array $options;

  /**
   * @phpstan-param array<string, string> $options
   */
  public function __construct(array $options) {
    $this->options = $options;
  }

  /**
   * @phpstan-return array<string, string>
   */
  public function getOptions(): array {
    return $this->options;
  }

  public function setOption(string $name, string $label): self {
    $this->options[$name] = $label;

    return $this;
  }

  /**
   * @phpstan-param array<string, string> $options
   */
  public function setOptions(array $options): self {
    $this->options = $options;

    return $this;
  }

}
