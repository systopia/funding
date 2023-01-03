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

namespace Civi\PHPUnit\Traits;

use PHPUnit\Framework\Assert;
use SebastianBergmann\Exporter\Exporter;

trait ArrayAssertTrait {

  /**
   * @phpstan-param array<int|string> $expectedKeys
   * @param mixed[] $actual
   */
  public static function assertArrayHasSameKeys(array $expectedKeys, array $actual): void {
    Assert::assertCount(count($expectedKeys), $actual);
    foreach ($expectedKeys as $expectedKey) {
      Assert::assertArrayHasKey($expectedKey, $actual);
    }
  }

  /**
   * @param mixed[] $expected
   * @param mixed[] $actual
   */
  public static function assertArrayHasSameValues(array $expected, array $actual): void {
    foreach (array_unique($expected, SORT_REGULAR) as $expectedValue) {
      $countExpected = count(array_keys($expected, $expectedValue, TRUE));
      $countActual = count(array_keys($actual, $expectedValue, TRUE));

      // check that expected value exists in the array
      Assert::assertSame(
        $countExpected,
        $countActual,
        sprintf(
          'Failed asserting that array contains %s %d times, actual is %d times.',
          self::getExporter()->export($expectedValue),
          $countExpected,
          $countActual
        )
      );
    }

    $extraValues = array_diff($actual, $expected);
    Assert::assertEmpty($extraValues, sprintf(
      'Actual array has additional values: %s',
      self::getExporter()->export($extraValues)
    ));
  }

  private static function getExporter(): Exporter {
    static $exporter;

    return $exporter ??= new Exporter();
  }

}
