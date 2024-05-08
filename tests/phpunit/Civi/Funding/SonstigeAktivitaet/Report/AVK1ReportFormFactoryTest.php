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

namespace Civi\Funding\SonstigeAktivitaet\Report;

use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\Validation\Traits\AssertValidationResultTrait;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\Report\AVK1ReportFormFactory
 */
final class AVK1ReportFormFactoryTest extends TestCase {

  use AssertFormTrait;

  use AssertValidationResultTrait;

  private JsonFormsFormInterface $form;

  protected function setUp(): void {
    parent::setUp();
    $formFactory = new AVK1ReportFormFactory();
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $this->form = $formFactory->createReportForm($clearingProcessBundle);
  }

  public function testSaveFieldsNotRequired(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $grunddaten = (object) [
      'titel' => 'Test',
      'kurzbeschreibungDesInhalts' => 'foo bar',
      'zeitraeume' => [
        (object) [
          'beginn' => '2022-08-24',
          'ende' => '2022-08-25',
        ],
      ],
      'teilnehmer' => (object) [],
    ];

    // With 'save' as action fields are not required.
    $data = (object) [
      '_action' => 'save',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'sachbericht' => (object) [
          'aenderungen' => '',
          'thematischeSchwerpunkte' => '',
        ],
        'dokumente' => (object) [
          'dateien' => [],
        ],
      ],
    ];
    static::assertValidationValid($validator->validate($data, $validationSchema));
  }

  public function testValidation(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $grunddaten = (object) [
      'titel' => 'Test',
      'kurzbeschreibungDesInhalts' => 'foo bar',
      'zeitraeume' => [
        (object) [
          'beginn' => '2022-08-24',
          'ende' => '2022-08-25',
        ],
      ],
      'teilnehmer' => (object) [
        'gesamt' => 5,
        'weiblich' => 4,
        'divers' => 3,
        'unter27' => 2,
        'inJugendhilfeEhrenamtlichTaetig' => 1,
        'inJugendhilfeHauptamtlichTaetig' => 0,
        'referenten' => 0,
      ],
    ];

    $dokumente = (object) [
      'dateien' => [
        (object) [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'sachbericht' => (object) [
          'aenderungen' => '',
          'thematischeSchwerpunkte' => '',
        ],
        'dokumente' => $dokumente,
      ],
    ];
    $result = $validator->validate($data, $validationSchema);
    static::assertFalse($result->isValid());

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'sachbericht' => (object) [
          'durchgefuehrt' => 'geplant',
          'aenderungen' => '',
          'thematischeSchwerpunkte' => 'abc',
          'methoden' => 'def',
          'zielgruppe' => 'ghi',
          'sonstiges' => 'jkl',
        ],
        'dokumente' => $dokumente,
      ],
    ];
    static::assertAllPropertiesSet($validationSchema, $data);
    $validator = OpisValidatorFactory::getValidator();
    static::assertValidationValid($validator->validate($data, $validationSchema));

    // 'aenderungen' is required if 'durchgefuehrt' is 'geändert'.
    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'sachbericht' => (object) [
          'durchgefuehrt' => 'geaendert',
          'aenderungen' => '',
          'thematischeSchwerpunkte' => 'abc',
          'methoden' => 'def',
          'zielgruppe' => 'ghi',
          'sonstiges' => 'jkl',
        ],
        'dokumente' => $dokumente,
      ],
    ];
    $validator = OpisValidatorFactory::getValidator();
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'sachbericht' => (object) [
          'durchgefuehrt' => 'geaendert',
          'aenderungen' => '123',
          'thematischeSchwerpunkte' => 'abc',
          'methoden' => 'def',
          'zielgruppe' => 'ghi',
          'sonstiges' => 'jkl',
        ],
        'dokumente' => $dokumente,
      ],
    ];
    static::assertTrue($validator->validate($data, $validationSchema)->isValid());
  }

  public function testUiSchema(): void {
    static::assertScopesExist($this->form->getJsonSchema()->toStdClass(), $this->form->getUiSchema());
  }

}
