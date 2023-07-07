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

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Funding\Traits;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;

trait CreateMockTrait {

  /**
   * Creates an APIv4 action mock that behaves (mostly) like the mocked class
   * itself. However, getParamInfo() is mocked because otherwise option
   * callbacks would be called that (might) require a complete Civi env.
   *
   * @template RealInstanceType of \Civi\Api4\Generic\AbstractAction
   * @phpstan-param class-string<RealInstanceType> $className
   * @phpstan-param mixed ...$constructorArgs
   *
   * @return \PHPUnit\Framework\MockObject\MockObject&RealInstanceType
   */
  public function createApi4ActionMock(string $className, ...$constructorArgs): MockObject {
    return $this->getMockBuilder($className)
      ->onlyMethods(['getParamInfo'])
      ->setConstructorArgs($constructorArgs)
      ->getMock();
  }

  /**
   * @template RealInstanceType of object
   * @param class-string<RealInstanceType> $originalClassName
   * @param array<string> $extraMethods
   *   Methods not declared in the class that you want to mock.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject&RealInstanceType
   */
  protected function createMockWithExtraMethods(string $originalClassName, array $extraMethods): MockObject {
    return $this->getMockBuilder($originalClassName)
      ->disableOriginalConstructor()
      ->disableOriginalClone()
      ->disableArgumentCloning()
      ->disallowMockingUnknownTypes()
      ->onlyMethods((new Generator())->getClassMethods($originalClassName))
      ->addMethods($extraMethods)
      ->getMock();
  }

}
