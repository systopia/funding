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

namespace Civi\Funding;

use Psr\Container\ContainerInterface;

class FundingCaseTypeServiceLocatorContainer {

  private ContainerInterface $serviceLocatorContainer;

  public function __construct(ContainerInterface $serviceLocatorContainer) {
    $this->serviceLocatorContainer = $serviceLocatorContainer;
  }

  public function get(string $fundingCaseType): FundingCaseTypeServiceLocatorInterface {
    /** @var \Civi\Funding\FundingCaseTypeServiceLocatorInterface $serviceLocator */
    $serviceLocator = $this->serviceLocatorContainer->get($fundingCaseType);

    return $serviceLocator;
  }

  public function has(string $fundingCaseType): bool {
    return $this->serviceLocatorContainer->has($fundingCaseType);
  }

}
