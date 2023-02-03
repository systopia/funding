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

namespace Civi\Funding\Validation;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Validation\EntityValidationResult
 * @covers \Civi\Funding\Validation\EntityValidationError
 */
final class EntityValidationResultTest extends TestCase {

  public function test(): void {
    $result = new EntityValidationResult();
    static::assertTrue($result->isValid());
    static::assertFalse($result->hasErrors());
    static::assertFalse($result->hasErrorsFor('test1'));

    static::assertSame([], $result->getErrors());
    static::assertSame([], $result->getErrorsFor('test1'));
    static::assertSame([], $result->getErrorsFlat());

    $error1 = EntityValidationError::new('test1', 'Foo1');
    $result->addError($error1);

    static::assertFalse($result->isValid());
    static::assertTrue($result->hasErrors());
    static::assertTrue($result->hasErrorsFor('test1'));
    static::assertFalse($result->hasErrorsFor('test2'));

    static::assertSame(['test1' => [$error1]], $result->getErrors());
    static::assertSame([$error1], $result->getErrorsFor('test1'));
    static::assertSame([], $result->getErrorsFor('test2'));
    static::assertSame([$error1], $result->getErrorsFlat());

    $error2 = EntityValidationError::new('test2', 'Foo2');
    $result->addErrors($error2);

    static::assertSame(['test1' => [$error1], 'test2' => [$error2]], $result->getErrors());
    static::assertSame([$error1, $error2], $result->getErrorsFlat());

    $error1X = EntityValidationError::new('test1', 'Foo1X');
    $result->addError($error1X);

    static::assertSame(['test1' => [$error1, $error1X], 'test2' => [$error2]], $result->getErrors());
    static::assertSame([$error1, $error1X], $result->getErrorsFor('test1'));
    static::assertSame([$error1, $error1X, $error2], $result->getErrorsFlat());
  }

  public function testMerge(): void {
    $error1 = EntityValidationError::new('test1', 'Foo1');
    $result1 = EntityValidationResult::new($error1);

    $error1X = EntityValidationError::new('test1', 'Foo1X');
    $error2 = EntityValidationError::new('test2', 'Foo2');
    $result2 = EntityValidationResult::new($error1X, $error2);

    $result1->merge($result2);
    static::assertSame(['test1' => [$error1, $error1X], 'test2' => [$error2]], $result1->getErrors());
  }

}
