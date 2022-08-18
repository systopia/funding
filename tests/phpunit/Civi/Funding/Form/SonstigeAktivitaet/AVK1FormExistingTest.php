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

namespace Civi\Funding\Form\SonstigeAktivitaet;

use Civi\Funding\Form\JsonSchema\JsonSchema;
use Civi\Funding\Form\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Form\SonstigeAktivitaet\AVK1FormExisting
 */
final class AVK1FormExistingTest extends TestCase {

  /**
   * @phpstan-var array<string, string>
   */
  private array $permissionActionMap = [
    'modify_application' => 'save',
    'apply_application' => 'apply',
  ];

  public function testPermission(): void {
    foreach ($this->permissionActionMap as $permission => $action) {
      $form = new AVK1FormExisting('€', 2, [$permission], []);

      $jsonSchema = $form->getJsonSchema();
      $properties = $jsonSchema->getKeywordValue('properties');
      static::assertInstanceOf(JsonSchema::class, $properties);
      static::assertEquals(new JsonSchemaString(['enum' => [$action]]), $properties->getKeywordValue('action'));
    }
  }

  public function testPermissionAll(): void {
    $form = new AVK1FormExisting('€', 2, array_keys($this->permissionActionMap), []);

    $jsonSchema = $form->getJsonSchema();
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertEquals(new JsonSchemaString(['enum' => array_values($this->permissionActionMap)]),
      $properties->getKeywordValue('action'));
  }

}
