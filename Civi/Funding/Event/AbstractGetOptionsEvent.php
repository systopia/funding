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

use Symfony\Contracts\EventDispatcher\Event;

/**
 * @codeCoverageIgnore
 *
 * @phpstan-type optionParamT array{
 *   id: int|string,
 *   name: string,
 *   label: string,
 *   abbr?: ?string,
 *   description?: ?string,
 *   icon?: ?string,
 *   color?: ?string,
 * }
 *
 * @phpstan-type optionT array{
 *   id: int|string,
 *   name: string,
 *   label: string,
 *   abbr: ?string,
 *   description: ?string,
 *   icon: ?string,
 *   color: ?string,
 * }
 */
abstract class AbstractGetOptionsEvent extends Event {

  /**
   * @phpstan-var array<string, optionT>
   *   Options with option name as key.
   */
  private array $options = [];

  /**
   * @phpstan-param array<optionParamT>|array<string, string> $options
   */
  public function __construct(array $options) {
    $this->setOptions($options);
  }

  /**
   * @phpstan-return optionT|null
   */
  public function getOption(string $name): ?array {
    return $this->options[$name] ?? NULL;
  }

  public function hasOption(string $name): bool {
    return isset($this->options[$name]);
  }

  /**
   * @phpstan-return list<optionT>
   */
  public function getOptions(): array {
    return array_values($this->options);
  }

  /**
   * @phpstan-param array<optionParamT>|array<string, string> $options
   */
  public function setOptions(array $options): self {
    $this->options = [];
    foreach ($options as $key => $value) {
      if (is_array($value)) {
        $this->addOption($value);
      }
      else {
        $this->addSimpleOption($key, $value);
      }
    }

    return $this;
  }

  /**
   * A possible option with the same name will be overridden.
   *
   * @phpstan-param optionParamT $option
   */
  public function addOption(array $option): self {
    $option += [
      'abbr' => NULL,
      'description' => NULL,
      'icon' => NULL,
      'color' => NULL,
    ];

    $this->options[$option['name']] = $option;

    return $this;
  }

  /**
   * A possible option with the same name will be overridden.
   */
  public function addSimpleOption(string $name, string $label): self {
    $this->addOption([
      'id' => $name,
      'name' => $name,
      'label' => $label,
    ]);

    return $this;
  }

}
