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
 * @covers \Civi\Funding\Translation\UiSchemaStringExtractor
 */
final class UiSchemaStringExtractorTest extends TestCase {

  public function test(): void {
    $extractor = new UiSchemaStringExtractor();

    // Extract label, description, and placeholder of control.
    static::assertEquals(
      ['Foo' => TRUE, 'Bar' => TRUE, 'Baz' => TRUE],
      $extractor->extractStrings(new JsonFormsControl(
      '#/test', 'Foo', 'Bar', ['placeholder' => 'Baz', 'x' => 'y']
      ))
    );

    // Extract label and confirm message of submit button.
    static::assertEquals(
      ['Foo' => TRUE, 'Confirm' => TRUE],
      $extractor->extractStrings(new JsonFormsSubmitButton(
        '#/test', 'foo', 'Foo', 'Confirm'
      ))
    );

    // Don't extract empty strings and NULL.
    static::assertSame([], $extractor->extractStrings(new JsonFormsControl(
      '#/test', '', NULL, ['placeholder' => '']
    )));

    // Extract label and description of layout as well as label, description,
    // and placeholder of contained control.
    static::assertEquals(
      [
        'Title' => TRUE,
        'Label' => TRUE,
        'Description1' => TRUE,
        'Description2' => TRUE,
        'x' => TRUE,
      ],
      $extractor->extractStrings(
        new JsonFormsGroup('Title', [
          new JsonFormsControl('#/test', 'Label', 'Description1', ['placeholder' => 'x']),
        ], 'Description2')
      )
    );

    // Extract labels of array control as well as label, description, and
    // placeholder of contained control.
    static::assertEquals(
      [
        'Array' => TRUE,
        'Add' => TRUE,
        'Remove' => TRUE,
        'Label' => TRUE,
        'Description' => TRUE,
        'x' => TRUE,
      ],
      $extractor->extractStrings(
        new JsonFormsArray('#/test', 'Array', NULL, [
          new JsonFormsControl('#/foo', 'Label', 'Description', ['placeholder' => 'x']),
        ], [
          'addButtonLabel' => 'Add',
          'removeButtonLabel' => 'Remove',
        ])
      )
    );

    // Extract content of markup.
    static::assertSame(
      ['Content' => TRUE],
      $extractor->extractStrings(new JsonFormsMarkup('Content'))
    );
  }

}
