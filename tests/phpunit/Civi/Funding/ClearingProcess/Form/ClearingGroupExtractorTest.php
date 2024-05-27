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

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\ClearingProcess\Form\Container\ClearingItemsGroup;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Form\ClearingGroupExtractor
 */
final class ClearingGroupExtractorTest extends TestCase {

  /**
   * @var \Civi\Funding\ClearingProcess\Form\ClearingGroupExtractor
   */
  private ClearingGroupExtractor $extractor;

  protected function setUp(): void {
    parent::setUp();
    $this->extractor = new ClearingGroupExtractor();
  }

  public function test(): void {
    $control1 = new JsonFormsControl('#/properties/control1', 'Control 1');
    $control2 = new JsonFormsControl('#/properties/control2', 'Control 2');
    $control3 = new JsonFormsControl('#/properties/control3', 'Control 3');
    $control4 = new JsonFormsControl('#/properties/control4', 'Control 4');
    $control5 = new JsonFormsControl('#/properties/control5', 'Control 5');
    $control6 = new JsonFormsControl('#/properties/control6', 'Control 6');

    $uiSchema = new JsonFormsGroup('Form', [
      new JsonFormsGroup('Group 1', [
        new JsonFormsGroup('', [
          $control1,
          $control2,
        ]),
        new JsonFormsGroup('Group 1A', [
          $control3,
        ]),
        $control4,
        new JsonFormsCategorization([
          new JsonFormsCategory('Category', [
            $control5,
          ]),
        ]),
      ]),
      new JsonFormsGroup('', [
        $control6,
      ]),
    ]);

    $scopes = [
      '#/properties/control2',
      '#/properties/control1',
    ];
    static::assertEquals(
      [
        new ClearingItemsGroup('Group 1', [
          '#/properties/control1' => $control1,
          '#/properties/control2' => $control2,
        ]),
      ],
      $this->extractor->extractGroups($uiSchema, $scopes)
    );

    $scopes = [
      '#/properties/control3',
      '#/properties/control2',
    ];
    static::assertEquals(
      [
        new ClearingItemsGroup('Group 1', [
          '#/properties/control2' => $control2,
        ]),
        new ClearingItemsGroup('Group 1A', [
          '#/properties/control3' => $control3,
        ]),
      ],
      $this->extractor->extractGroups($uiSchema, $scopes)
    );

    $scopes = [
      '#/properties/control4',
      '#/properties/control3',
      '#/properties/control2',
    ];
    static::assertEquals(
      [
        new ClearingItemsGroup('Group 1', [
          '#/properties/control2' => $control2,
          '#/properties/control4' => $control4,
        ]),
        new ClearingItemsGroup('Group 1A', [
          '#/properties/control3' => $control3,
        ]),
      ],
      $this->extractor->extractGroups($uiSchema, $scopes)
    );

    $scopes = [
      '#/properties/control4',
      '#/properties/control3',
    ];
    static::assertEquals(
      [
        new ClearingItemsGroup('Group 1A', [
          '#/properties/control3' => $control3,
        ]),
        new ClearingItemsGroup('Group 1', [
          '#/properties/control4' => $control4,
        ]),
      ],
      $this->extractor->extractGroups($uiSchema, $scopes)
    );

    $scopes = [
      '#/properties/control6',
      '#/properties/control5',
    ];
    static::assertEquals(
      [
        new ClearingItemsGroup(NULL, [
          '#/properties/control5' => $control5,
        ]),
        new ClearingItemsGroup(NULL, [
          '#/properties/control6' => $control6,
        ]),
      ],
      $this->extractor->extractGroups($uiSchema, $scopes)
    );
  }

}
