<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\MappedData;

use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Tags\TaggedDataContainer;

/**
 * @covers \Civi\Funding\Form\MappedData\MappedDataLoader
 */
final class MappedDataLoaderTest extends TestCase {

  private MappedDataLoader $mappedDataLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->mappedDataLoader = new MappedDataLoader();
  }

  public function testSingleValue(): void {
    $taggedData = new TaggedDataContainer();
    $taggedData->add('mapToField', '/test', 'data', (object) ['fieldName' => 'field']);

    static::assertSame(['field' => 'data'], $this->mappedDataLoader->getMappedData($taggedData));
  }

  public function testReplace(): void {
    $taggedData = new TaggedDataContainer();
    $taggedData->add('mapToField', '/test1', 'data1', (object) ['fieldName' => 'field']);
    $taggedData->add('mapToField', '/test2', 'data2', (object) ['fieldName' => 'field']);
    // replace is true by default.
    static::assertSame(['field' => 'data2'], $this->mappedDataLoader->getMappedData($taggedData));

    $taggedData->add('mapToField', '/test3', 'data3', (object) ['fieldName' => 'field', 'replace' => FALSE]);
    static::assertSame(['field' => 'data2'], $this->mappedDataLoader->getMappedData($taggedData));

    $taggedData->add('mapToField', '/test4', 'data4', (object) ['fieldName' => 'field', 'replace' => TRUE]);
    static::assertSame(['field' => 'data4'], $this->mappedDataLoader->getMappedData($taggedData));
  }

  public function testMultiple(): void {
    $taggedData = new TaggedDataContainer();
    $taggedData->add('mapToField', '/test/0', 'data1', (object) ['fieldName' => 'field', 'multiple' => TRUE]);
    static::assertSame(['field' => ['data1']], $this->mappedDataLoader->getMappedData($taggedData));

    $taggedData->add('mapToField', '/test/1', 'data2', (object) ['fieldName' => 'field', 'multiple' => TRUE]);
    static::assertSame(['field' => ['data1', 'data2']], $this->mappedDataLoader->getMappedData($taggedData));
  }

  public function testReplaceMultiple(): void {
    $taggedData = new TaggedDataContainer();
    $taggedData->add('mapToField', '/single1', 'data1', (object) ['fieldName' => 'field']);
    static::assertSame(['field' => 'data1'], $this->mappedDataLoader->getMappedData($taggedData));

    $taggedData->add('mapToField', '/multiple', 'data2', (object) ['fieldName' => 'field', 'multiple' => TRUE]);
    static::assertSame(['field' => ['data2']], $this->mappedDataLoader->getMappedData($taggedData));

    $taggedData->add('mapToField', '/single2', 'data3', (object) ['fieldName' => 'field']);
    static::assertSame(['field' => 'data3'], $this->mappedDataLoader->getMappedData($taggedData));
  }

}
