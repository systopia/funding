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

namespace Civi\Funding\Form\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\JsonSchema
 */
final class JsonSchemaTest extends TestCase {

  public function testAddKeyword(): void {
    $schema = new JsonSchema([]);
    $schema->addKeyword('foo', 'bar');
    static::assertSame('bar', $schema->getKeywordValue('foo'));
    static::assertSame(['foo' => 'bar'], $schema->getKeywords());
    static::assertTrue($schema->hasKeyword('foo'));
    static::assertFalse($schema->hasKeyword('bar'));

    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('Keyword "foo" already exists');
    $schema->addKeyword('foo', 'bar2');
  }

  public function testGetMissingKeyword(): void {
    $schema = new JsonSchema([]);
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('No such keyword "foo"');
    $schema->getKeywordValue('foo');
  }

  public function testFromArray(): void {
    $array = [
      'foo' => [
        'bar' => 'baz',
      ],
      'fuu' => [2, NULL, 'test', TRUE],
    ];
    $schema = JsonSchema::fromArray($array);
    $keywords = $schema->getKeywords();
    static::assertSame(['foo', 'fuu'], array_keys($keywords));
    static::assertInstanceOf(JsonSchema::class, $keywords['foo']);
    static::assertSame(['bar' => 'baz'], $keywords['foo']->getKeywords());
    static::assertSame([2, NULL, 'test', TRUE], $keywords['fuu']);
  }

  public function testFromArrayInvalid01(): void {
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('Expected associative array got non-associative array');
    JsonSchema::fromArray(['foo' => [['invalid']]]);
  }

  public function testFromArrayInvalid02(): void {
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage(sprintf(
      'Expected scalar, %s, NULL, or non-associative array containing those three types, got "stdClass"',
      JsonSchema::class
    ));
    JsonSchema::fromArray(['foo' => new \stdClass()]);
  }

  public function testToArray(): void {
    $schema = new JsonSchema([
      'foo' => new JsonSchema(['bar' => 'baz']),
      'fuu' => [1, 2, new JsonSchema(['keyword' => 'value']), TRUE],
      'f00' => NULL,
    ]);
    $expected = [
      'foo' => ['bar' => 'baz'],
      'fuu' => [1, 2, ['keyword' => 'value'], TRUE],
      'f00' => NULL,
    ];
    static::assertSame($expected, $schema->toArray());
  }

  public function testToStdClass(): void {
    $schema = new JsonSchema([
      'foo' => new JsonSchema(['bar' => 'baz']),
      'fuu' => [1, 2, new JsonSchema(['keyword' => 'value']), TRUE],
      'f00' => NULL,
    ]);
    $expected = (object) [
      'foo' => (object) ['bar' => 'baz'],
      'fuu' => [1, 2, (object) ['keyword' => 'value'], TRUE],
      'f00' => NULL,
    ];
    static::assertEquals($expected, $schema->toStdClass());
  }

  public function testJsonSerialize(): void {
    $schema = new JsonSchema([
      'foo' => new JsonSchema(['bar' => 'baz']),
      'fuu' => [1, 2, new JsonSchema(['keyword' => 'value'])],
      'f00' => NULL,
    ]);
    $expected = json_encode([
      'foo' => ['bar' => 'baz'],
      'fuu' => [1, 2, ['keyword' => 'value']],
      'f00' => NULL,
    ]);
    static::assertSame($expected, json_encode($schema));
  }

  public function testConvertToJsonSchemaArray(): void {
    $array = ['foo', 2, ['bar' => 'baz'], FALSE];
    $schemaArray = JsonSchema::convertToJsonSchemaArray($array);
    $expected = ['foo', 2, new JsonSchema(['bar' => 'baz']), FALSE];
    static::assertEquals($expected, $schemaArray);
  }

  public function testConvertToJsonSchemaArrayInvalid(): void {
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('Expected associative array got non-associative array');
    JsonSchema::convertToJsonSchemaArray([['invalid']]);
  }

}
