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

namespace Civi\Funding;

use Civi\Core\Container as CiviContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Allows to run tests that need to fetch services from the container. The
 * container needs to be mocked appropriately.
 */
abstract class AbstractContainerMockedTestCase extends TestCase {

  /**
   * @var \Psr\Container\ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected ContainerInterface $containerMock;

  protected function setUp(): void {
    parent::setUp();
    \Civi::$statics[CiviContainer::class]['container'] = $this->containerMock = $this->createContainer();
  }

  protected function tearDown(): void {
    parent::tearDown();
    \Civi::$statics[CiviContainer::class]['container'] = NULL;
  }

  /**
   * @return \Psr\Container\ContainerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected function createContainer(): ContainerInterface {
    return $this->createMock(ContainerInterface::class);
  }

}
