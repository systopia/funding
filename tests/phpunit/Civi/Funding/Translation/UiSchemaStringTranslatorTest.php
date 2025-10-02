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

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Translation\UiSchemaStringTranslator
 */
final class UiSchemaStringTranslatorTest extends TestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  public function testTranslateStrings(): void {
    $translator = new UiSchemaStringTranslator();

    // Translate label, description, and placeholder of control.
    $control = new JsonFormsControl(
      '#/test', 'Foo', 'Bar', ['placeholder' => 'Baz']
    );
    $translator->translateStrings($control, ['Foo' => 'Foo2', 'Bar' => 'Bar2', 'Baz' => 'Baz2'], 'en_US');
    static::assertSame('Foo2', $control['label']);
    static::assertSame('Bar2', $control['description']);
    static::assertSame('Baz2', $control['options']['placeholder'] ?? NULL);

    // Translate label and confirm message of submit button.
    $button = new JsonFormsSubmitButton('#/test', 'foo', 'Foo', 'Confirm');
    $translator->translateStrings($button, ['Foo' => 'Foo2', 'Confirm' => 'Confirm2'], 'en_US');
    static::assertSame('Foo2', $button['label']);
    static::assertSame('Confirm2', $button['options']['confirm'] ?? NULL);

    // Translate layout and contained control.
    $control = new JsonFormsControl('#/test', 'Label', 'DescriptionC', ['placeholder' => 'x']);
    $layout = new JsonFormsGroup('Title', [$control], 'DescriptionL');
    $translator->translateStrings($layout, [
      'Title' => 'Title2',
      'DescriptionL' => 'DescriptionL2',
      'Label' => 'Label2',
      'x' => 'y',
    ], 'en_US');
    static::assertSame('Title2', $layout['label']);
    static::assertSame('DescriptionL2', $layout['description']);
    static::assertSame('Label2', $control['label']);
    // Description is unchanged because there's no translation.
    static::assertSame('DescriptionC', $control['description']);
    static::assertSame('y', $control['options']['placeholder'] ?? NULL);

    // Translate labels of array as well as label of contained control.
    $control = new JsonFormsControl('#/foo', 'Label');
    $array = new JsonFormsArray('#/test', 'Array', NULL, [
      $control,
    ], [
      'addButtonLabel' => 'Add',
      'removeButtonLabel' => 'Remove',
    ]);
    $translator->translateStrings($array, [
      'Array' => 'Array2',
      'Add' => 'Add2',
      'Remove' => 'Remove2',
      'Label' => 'Label2',
    ], 'en_US');
    static::assertSame('Array2', $array['label']);
    static::assertSame('Add2', $array['options']['addButtonLabel'] ?? NULL);
    static::assertSame('Remove2', $array['options']['removeButtonLabel']);
    static::assertSame('Label2', $control['label']);

    // Translate content of markup.
    $markup = new JsonFormsMarkup('Content');
    $translator->translateStrings($markup, ['Content' => 'Content2'], 'en_US');
    static::assertSame('Content2', $markup['content']);
  }

}
