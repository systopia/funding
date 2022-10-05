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

namespace Civi\Funding\Form\Traits;

use Civi\Funding\Util\FormTestUtil;
use Civi\RemoteTools\Form\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonForms\JsonFormsElement;
use Civi\RemoteTools\Form\JsonForms\JsonFormsLayout;
use Opis\JsonSchema\JsonPointer;
use PHPUnit\Framework\Assert;

trait AssertFormTrait {

  /**
   * Asserts that all properties (including non-required) specified in JSON
   * schema are set.
   *
   * @param \stdClass $jsonSchema
   * @param mixed $data
   * @param string $path
   */
  public static function assertAllPropertiesSet(\stdClass $jsonSchema, $data, string $path = ''): void {
    if (!property_exists($jsonSchema, 'type')) {
      return;
    }

    if ('object' === $jsonSchema->type) {
      Assert::assertInstanceOf(\stdClass::class, $data,
        sprintf('Expected instanceof \\stdClass at path "%s"', $path));
      foreach ($jsonSchema->properties as $key => $childSchema) {
        $path = $path . '/' . $key;
        Assert::assertObjectHasAttribute($key, $data, sprintf('No value at path "%s"', $path));
        self::assertAllPropertiesSet($childSchema, $data->{$key}, $path);
      }
    }
    elseif ('array' === $jsonSchema->type) {
      Assert::assertIsArray($data, sprintf('Expected array at path "%s"', $path));
      Assert::assertNotEmpty($data, sprintf('Expected non-empty array at path "%s"', $path));
      foreach ($data as $key => $value) {
        Assert::assertIsInt($key, sprintf('Expected non-associative array at path "%s"', $path));
        static::assertAllPropertiesSet($value, $jsonSchema->items, $path . '/' . $key);
      }
    }
    else {
      Assert::assertIsScalar($data, sprintf('Expected a scalar at path "%s"', $path));
    }
  }

  public static function assertControlInSchemaEquals(JsonFormsControl $expected, JsonFormsElement $uiSchema): void {
    $actual = FormTestUtil::getFirstControlWithScope($expected->getScope(), $uiSchema);
    Assert::assertEquals($expected, $actual);
  }

  public static function assertScopeExists(string $scope, JsonFormsElement $uiSchema): void {
    Assert::assertNotNull(
      FormTestUtil::getFirstControlWithScope($scope, $uiSchema),
      sprintf('Scope "%s" does not exist in UI schema', $scope)
    );
  }

  /**
   * Asserts that all scopes specified in UI schema have a corresponding
   * definition in JSON schema.
   *
   * @param \stdClass $jsonSchema
   * @param \Civi\RemoteTools\Form\JsonForms\JsonFormsElement $uiSchema
   */
  public static function assertScopesExist(\stdClass $jsonSchema, JsonFormsElement $uiSchema): void {
    if ($uiSchema instanceof JsonFormsControl) {
      $scope = $uiSchema->getScope();
      $pointer = JsonPointer::parse(substr($scope, 1));
      Assert::assertNotNull($pointer, sprintf('Invalid scope "%s"', $scope));
      $property = $pointer->data($jsonSchema);
      Assert::assertInstanceOf(\stdClass::class, $property, sprintf('Invalid scope "%s"', $scope));

      if ($uiSchema instanceof JsonFormsArray) {
        Assert::assertSame('array', $property->type);
        foreach ($uiSchema->getElements() ?? [] as $childElement) {
          self::assertScopesExist($property->items, $childElement);
        }
      }
    }
    elseif ($uiSchema instanceof JsonFormsLayout) {
      foreach ($uiSchema->getElements() as $childElement) {
        self::assertScopesExist($jsonSchema, $childElement);
      }
    }
  }

}
