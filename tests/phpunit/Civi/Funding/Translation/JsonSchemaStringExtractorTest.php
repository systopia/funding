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
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Translation\JsonSchemaStringExtractor
 */
final class JsonSchemaStringExtractorTest extends TestCase {

  public function test(): void {
    $extractor = new JsonSchemaStringExtractor();

    // Extract title of oneOf items.
    static::assertEquals(
      ['Foo' => TRUE],
      $extractor->extractStrings(new JsonSchemaString([
        'oneOf' => JsonSchema::convertToJsonSchemaArray([
          ['const' => 'foo', 'title' => 'Foo'],
        ]),
      ]))
    );

    // Don't extract empty string.
    static::assertEquals(
      [],
      $extractor->extractStrings(new JsonSchemaString([
        'oneOf' => JsonSchema::convertToJsonSchemaArray([
          ['const' => 'foo', 'title' => ''],
        ]),
      ]))
    );

    // Don't fail with oneOf items without title.
    static::assertSame(
      [],
      $extractor->extractStrings(new JsonSchemaString([
        'oneOf' => ['foo'],
      ]))
    );

    // Extract validation message in object property.
    static::assertEquals(
      ['Message' => TRUE],
      $extractor->extractStrings(new JsonSchemaObject([
        'string' => new JsonSchemaString([
          '$validations' => JsonSchema::convertToJsonSchemaArray([
            [
              'keyword' => 'const',
              'value' => 'Test',
              'message' => 'Message',
            ],
          ]),
        ]),
      ]))
    );

    // Extract validation message in array items schema.
    static::assertEquals(
      ['Message' => TRUE],
      $extractor->extractStrings(new JsonSchemaArray(
        new JsonSchemaString([
          '$validations' => JsonSchema::convertToJsonSchemaArray([
            [
              'keyword' => 'const',
              'value' => 'Test',
              'message' => 'Message',
            ],
          ]),
        ])
      ))
    );
  }

}
