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
 * @covers \Civi\Funding\Translation\StringExtractUtil
 */
final class StringExtractUtilTest extends TestCase {

  public function testAddStrings(): void {
    $strings = [];
    StringExtractUtil::addStrings($strings, JsonSchema::fromArray(['label' => 'foo']), ['label']);
    static::assertSame(['foo' => TRUE], $strings);

    // Extract string from sub-schema.
    $strings = [];
    StringExtractUtil::addStrings(
      $strings,
      JsonSchema::fromArray(['foo' => ['label' => 'foo']]),
      [['foo', 'label']]
    );
    static::assertSame(['foo' => TRUE], $strings);

    // Extract string from sub-schema.
    $strings = [];
    StringExtractUtil::addStrings(
      $strings,
      JsonSchema::fromArray(['foo' => ['label' => 'foo']]),
      ['foo/label']
    );
    static::assertSame(['foo' => TRUE], $strings);

    // Don't fail if path is invalid.
    $strings = [];
    StringExtractUtil::addStrings($strings, JsonSchema::fromArray(['label' => 'foo']), ['label/x']);
    static::assertSame([], $strings);

    // Don't fail if path is invalid.
    $strings = [];
    StringExtractUtil::addStrings($strings, JsonSchema::fromArray(['label' => 'foo']), ['x']);
    static::assertSame([], $strings);

    // Extract string that may contain placeholders.
    $strings = [];
    StringExtractUtil::addStrings($strings, JsonSchema::fromArray(['label' => ['text' => 'foo']]), ['label']);
    static::assertSame(['foo' => TRUE], $strings);

    // Extract list of translatable strings.
    $strings = [];
    StringExtractUtil::addStrings($strings, JsonSchema::fromArray(['texts' => ['foo', 'bar']]), ['texts']);
    static::assertSame(['foo' => TRUE, 'bar' => TRUE], $strings);
  }

}
