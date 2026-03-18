<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseType;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template T of \Civi\Funding\FundingCaseType\FundingCaseTypeServiceInterface
 */
abstract class AbstractFundingCaseTypeServiceCollector {

  private ContainerInterface $container;

  /**
   * @param \Psr\Container\ContainerInterface $container
   *   Services with funding case type name as ID. The ID "*" is used as
   *   fallback.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * @return T
   */
  protected function getService(string $fundingCaseTypeName): object {
    try {
      // @phpstan-ignore return.type
      return $this->container->get($fundingCaseTypeName);
    }
    catch (NotFoundExceptionInterface $e) {
      try {
        // @phpstan-ignore return.type
        return $this->container->get('*');
      }
      catch (NotFoundExceptionInterface) {
        throw $e;
      }
    }
  }

  protected function hasService(string $fundingCaseTypeName): bool {
    return $this->container->has($fundingCaseTypeName) || $this->container->has('*');
  }

}
