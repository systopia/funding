<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseType;

use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Psr\Container\ContainerInterface;

final class FundingCaseTypeMetaDataProvider implements FundingCaseTypeMetaDataProviderInterface {

  private ContainerInterface $container;

  /**
   * @phpstan-var list<string>
   */
  private array $names;

  /**
   * @phpstan-param list<string> $names
   */
  public function __construct(ContainerInterface $container, array $names) {
    $this->container = $container;
    $this->names = $names;
  }

  public function get(string $name): FundingCaseTypeMetaDataInterface {
    // @phpstan-ignore return.type
    return $this->container->get($name);
  }

  public function getAll(): iterable {
    foreach ($this->names as $name) {
      yield $name => $this->get($name);
    }
  }

  public function getNames(): array {
    return $this->names;
  }

  public function has(string $name): bool {
    return in_array($name, $this->names, TRUE);
  }

}
