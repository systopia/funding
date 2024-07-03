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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\Funding\Form\JsonSchema\JsonFormsSubmitButtonsFactory;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\IJB\Application\JsonSchema\IJBApplicationJsonSchema;
use Civi\Funding\Util\FormTestUtil;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\IJB\Application\UiSchema\IJBApplicationUiSchema
 */
final class IJBApplicationUiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function testUiSchema(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $submitActions = [
      'submitAction1' => ['label' => 'Do Submit1', 'confirm' => NULL],
      'submitAction2' => ['label' => 'Do Submit2', 'confirm' => 'Proceed?'],
    ];

    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      $possibleRecipients,
      ['_action' => new JsonSchemaString()],
    );

    $uiSchema = new IJBApplicationUiSchema('EUR', JsonFormsSubmitButtonsFactory::createButtons($submitActions));

    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
    static::assertScopeExists('#/properties/_action', $uiSchema);

    static::assertEquals(
      [
        new JsonFormsSubmitButton('#/properties/_action', 'submitAction1', 'Do Submit1'),
        new JsonFormsSubmitButton('#/properties/_action', 'submitAction2', 'Do Submit2', 'Proceed?'),
      ],
      FormTestUtil::getControlsWithScope('#/properties/_action', $uiSchema)
    );
  }

}
