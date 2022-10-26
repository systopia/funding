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

use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\RemoteTools\Form\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaInteger;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Form\SonstigeAktivitaet\AVK1FormExisting
 */
final class AVK1FormExistingTest extends TestCase {

  use AssertFormTrait;

  public function test(): void {
    $form = new AVK1FormExisting(new \DateTime('2022-08-24'), new \DateTime('2022-08-25'),
      '€', 2, [1 => 'Recipient'], ['save' => ['label' => 'Save']], FALSE, []);

    $jsonSchema = $form->getJsonSchema();
    $properties = $jsonSchema->getKeywordValue('properties');
    static::assertInstanceOf(JsonSchema::class, $properties);
    static::assertEquals(
      new JsonSchemaInteger(['const' => 2, 'readOnly' => TRUE]),
      $properties->getKeywordValue('applicationProcessId')
    );
    static::assertEquals(new JsonSchemaRecipient([1 => 'Recipient']), $properties->getKeywordValue('empfaenger'));

    static::assertFalse($form->isReadOnly());
    static::assertScopesExist($jsonSchema->toStdClass(), $form->getUiSchema());

    static::assertControlInSchemaEquals(
      new JsonFormsHidden('#/properties/applicationProcessId'),
      $form->getUiSchema()
    );
  }

  public function testReadOnly(): void {
    $form = new AVK1FormExisting(new \DateTime('2022-08-24'), new \DateTime('2022-08-25'),
      '€', 2, [1 => 'Recipient'], ['save' => ['label' => 'Save']], TRUE, []);

    static::assertTrue($form->isReadOnly());
  }

}
