<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Translation;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Translation\StringTranslateUtil
 */
final class StringTranslateUtilTest extends TestCase {

  public function testTranslateStrings(): void {
    $schema = new JsonSchema(['test' => 'original']);

    // Don't fail if path doesn't exist.
    StringTranslateUtil::translateStrings($schema, ['foo'], ['original' => 'translation'], 'en_US');
    static::assertSame('original', $schema['test']);

    // Nothing is changed if translations don't contain the string.
    StringTranslateUtil::translateStrings($schema, ['test'], ['foo' => 'translation'], 'en_US');
    static::assertSame('original', $schema['test']);

    // Translate string.
    StringTranslateUtil::translateStrings($schema, ['test'], ['original' => 'translation'], 'en_US');
    static::assertSame('translation', $schema['test']);

    // Strings to translate are trimmed.
    $schema = new JsonSchema(['test' => ' original ']);
    StringTranslateUtil::translateStrings($schema, ['test'], ['original' => 'translation'], 'en_US');
    static::assertSame('translation', $schema['test']);

    $schema = JsonSchema::fromArray([
      'foo1' => ['bar' => 'original1', 'baz' => 1.23],
      'foo2' => ['bar' => 'original2'],
      'foo3' => ['item'],
    ]);
    // Translate strings at sub-paths.
    StringTranslateUtil::translateStrings(
      $schema,
      [['foo1', 'bar'], 'foo2/bar'],
      ['original1' => 'translation1', 'original2' => 'translation2'],
    'en_US'
    );
    // @phpstan-ignore offsetAccess.nonOffsetAccessible
    static::assertSame('translation1', $schema['foo1']['bar']);
    // @phpstan-ignore offsetAccess.nonOffsetAccessible
    static::assertSame('translation2', $schema['foo2']['bar']);

    // Don't fail if path doesn't exist.
    StringTranslateUtil::translateStrings($schema, ['foo3/item'], [], 'en_US');

    // Don't fail if path contains no string.
    StringTranslateUtil::translateStrings($schema, ['foo1/baz'], [], 'en_US');

    // String with placeholder is formatted.
    $schema = JsonSchema::fromArray(['test' => ['text' => 'Hello {name}', 'values' => ['name' => 'Peter']]]);
    StringTranslateUtil::translateStrings($schema, ['test'], ['Hello {name}' => 'Hallo {name}'], 'en_US');
    static::assertSame('Hallo Peter', $schema['test']);

    $schema = JsonSchema::fromArray(['test' => ['text' => '{x, number}', 'values' => ['x' => 1.23]]]);
    StringTranslateUtil::translateStrings($schema, ['test'], [], 'en_US');
    static::assertSame('1.23', $schema['test']);

    $schema = JsonSchema::fromArray([
      'test' => [
        'text' => '{x, number}',
        'values' => ['x' => 1.23],
        'locale' => 'de_DE',
      ],
    ]);
    StringTranslateUtil::translateStrings($schema, ['test'], [], 'en_US');
    static::assertSame('1,23', $schema['test']);
  }

}
