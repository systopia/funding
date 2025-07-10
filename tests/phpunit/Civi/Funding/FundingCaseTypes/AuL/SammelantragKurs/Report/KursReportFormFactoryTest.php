<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report;

use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Form\MappedData\MappedDataLoader;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\Validation\Traits\AssertValidationResultTrait;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use Civi\RemoteTools\JsonSchema\Validation\Validator;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Translation\NullTranslator;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Report\KursReportFormFactory
 */
final class KursReportFormFactoryTest extends TestCase {

  use AssertFormTrait;

  use AssertValidationResultTrait;

  private JsonFormsFormInterface $form;

  protected function setUp(): void {
    parent::setUp();
    $formFactory = new KursReportFormFactory();
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $this->form = $formFactory->createReportForm($clearingProcessBundle);
  }

  public function testSaveFieldsNotRequired(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $grunddaten = (object) [
      'titel' => 'Test',
      'kurzbeschreibungDerInhalte' => 'foo bar',
      'internerBezeichner' => 'interne id',
      'zeitraeume' => [
        (object) [
          'beginn' => '2022-08-24',
          'ende' => '2022-08-24',
        ],
        (object) [
          'beginn' => '2022-08-25',
          'ende' => '2022-08-26',
        ],
      ],
      'teilnehmer' => (object) [
        'gesamt' => 5,
        'referenten' => 2,
      ],
    ];

    // With 'save' as action fields are not required.
    $data = (object) [
      '_action' => 'save',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'dokumente' => (object) [
          'dateien' => [],
        ],
        'foerderung' => (object) [],
      ],
    ];
    static::assertValidationValid($validator->validate($data, $validationSchema));
  }

  public function testValidation(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $programmtage = 3;
    $teilnehmerGesamt = 5;
    $teilnehmerMitFahrtkosten  = 4;
    $referenten = 2;
    $referentenMitHonorar = 1;
    $teilnehmerkostenMax = $programmtage * $teilnehmerGesamt * 40;
    $fahrtkostenMax = $teilnehmerMitFahrtkosten * 60;
    $honorarkostenMax = $programmtage * $referentenMitHonorar * 305;

    $grunddaten = (object) [
      'titel' => 'Test',
      'kurzbeschreibungDerInhalte' => 'foo bar',
      'internerBezeichner' => 'interne id',
      'zeitraeume' => [
        (object) [
          'beginn' => '2022-08-24',
          'ende' => '2022-08-24',
        ],
        (object) [
          'beginn' => '2022-08-25',
          'ende' => '2022-08-26',
        ],
      ],
      'teilnehmer' => (object) [
        'gesamt' => $teilnehmerGesamt,
        'weiblich' => 4,
        'divers' => 3,
        'unter27' => 2,
        'inJugendhilfeEhrenamtlichTaetig' => 1,
        'inJugendhilfeHauptamtlichTaetig' => 0,
        'referenten' => $referenten,
        'referentenMitHonorar' => $referentenMitHonorar,
        'mitFahrtkosten' => $teilnehmerMitFahrtkosten,
      ],
    ];

    $beschreibung = (object) [
      'ziele' => [
        'persoenlichkeitsbildung',
        'internationaleBegegnungen',
      ],
      'bildungsanteil' => 22,
      'veranstaltungsort' => 'Veranstaltungsort',
      'kooperationspartner' => 'Kooperationspartner',
    ];

    $dokumente = (object) [
      'dateien' => [
        (object) [
          'datei' => 'https://example.org/test.txt',
          'beschreibung' => 'Test',
        ],
      ],
    ];

    $foerderung = (object) [
      'teilnahmetage' => 1,
      'honorare' => 2,
      'fahrtkosten' => 3,
    ];

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'beschreibung' => $beschreibung,
        'zuschuss' => (object) [],
        'dokumente' => $dokumente,
        'foerderung' => $foerderung,
      ],
    ];

    $result = $validator->validate($data, $validationSchema);
    static::assertValidationValid($result);
    static::assertSame($teilnehmerkostenMax, $data->reportData->zuschuss->teilnehmerkostenMax);
    static::assertSame($fahrtkostenMax, $data->reportData->zuschuss->fahrtkostenMax);
    static::assertSame($honorarkostenMax, $data->reportData->zuschuss->honorarkostenMax);
    static::assertSame($programmtage, $data->reportData->grunddaten->programmtage);
    static::assertSame(6, $data->reportData->foerderung->summe);
    static::assertAllPropertiesSet($validationSchema, $data);

    $tagValidator = new Validator(new NullTranslator(), OpisValidatorFactory::getValidator());
    $result = $tagValidator->validate($this->form->getJsonSchema(), get_object_vars($data));

    $mappedDataLoader = new MappedDataLoader();
    $mappedData = $mappedDataLoader->getMappedData($result->getTaggedData());

    static::assertEquals([
      'title' => 'Test',
      'short_description' => 'foo bar',
      'start_date' => '2022-08-24',
      'end_date' => '2022-08-26',
    ], $mappedData);
  }

  public function testUiSchema(): void {
    static::assertScopesExist($this->form->getJsonSchema()->toStdClass(), $this->form->getUiSchema());
  }

}
