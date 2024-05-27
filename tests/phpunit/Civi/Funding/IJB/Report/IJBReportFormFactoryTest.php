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

namespace Civi\Funding\IJB\Report;

use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\Validation\Traits\AssertValidationResultTrait;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\IJB\Report\IJBReportFormFactory
 */
final class IJBReportFormFactoryTest extends TestCase {

  use AssertFormTrait;

  use AssertValidationResultTrait;

  private JsonFormsFormInterface $form;

  protected function setUp(): void {
    parent::setUp();
    $formFactory = new IJBReportFormFactory();
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $this->form = $formFactory->createReportForm($clearingProcessBundle);
  }

  public function testSaveFieldsNotRequired(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    // With 'save' as action fields are not required.
    $data = (object) [
      '_action' => 'save',
      'reportData' => (object) [
        'sachbericht' => (object) [
          'aenderungen' => '',
          'verstaendigungFreitext' => '',
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

    $programmtageMitHonorar = 2;
    $fahrtstreckeInKm = 100;
    $teilnehmerDeutschlandGesamt = 10;
    $teilnehmerDeutschlandMitFahrtkosten = 99;
    $teilnehmerPartnerlandGesamt = 11;
    $teilnehmerGesamt = $teilnehmerDeutschlandGesamt + $teilnehmerPartnerlandGesamt;

    $grunddaten = (object) [
      'titel' => 'Test',
      'kurzbeschreibungDesInhalts' => 'foo bar',
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
      'programmtageMitHonorar' => 2,
      'artDerMassnahme' => 'fachkraefteprogramm',
      'begegnungsland' => 'deutschland',
      'stadt' => 'Stadt',
      'land' => 'Land',
      'fahrtstreckeInKm' => $fahrtstreckeInKm,
    ];

    $teilnehmer = (object) [
      'deutschland' => (object) [
        'gesamt' => $teilnehmerDeutschlandGesamt,
        'weiblich' => 4,
        'divers' => 3,
        'unter27' => 2,
        'inJugendhilfeEhrenamtlichTaetig' => 1,
        'inJugendhilfeHauptamtlichTaetig' => 0,
        'referenten' => 5,
        'mitFahrtkosten' => $teilnehmerDeutschlandMitFahrtkosten,
      ],
      'partnerland' => (object) [
        'gesamt' => $teilnehmerPartnerlandGesamt,
        'weiblich' => 4,
        'divers' => 3,
        'unter27' => 2,
        'inJugendhilfeEhrenamtlichTaetig' => 1,
        'inJugendhilfeHauptamtlichTaetig' => 0,
        'referenten' => 5,
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
        'teilnehmer' => $teilnehmer,
        'zuschuss' => (object) [],
        'sachbericht' => (object) [
          'aenderungen' => '',
          'verstaendigungFreitext' => '',
        ],
        'dokumente' => $dokumente,
        'foerderung' => (object) [],
      ],
    ];
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'teilnehmer' => $teilnehmer,
        'zuschuss' => (object) [],
        'sachbericht' => (object) [
          'durchgefuehrt' => 'geplant',
          'form' => 'praesenz',
          'aenderungen' => '',
          'sprache' => 'partnersprache',
          'andereSprache' => '',
          'verstaendigungBewertung' => 'gut',
          'verstaendigungFreitext' => '',
          'sprachlicheUnterstuetzung' => FALSE,
          'sprachlicheUnterstuetzungArt' => '',
          'sprachlicheUnterstuetzungProgrammpunkte' => '',
          'sprachlicheUnterstuetzungErfahrungen' => '',
          'vorbereitung' => 'v',
          'vorbereitungstreffen' => TRUE,
          'vorbereitungstreffenFreitext' => '',
          'vorbereitungTeilnehmer' => 'vT',
          'themenfelder' => ['politik'],
          'zieleErreicht' => 'zE',
          'intensiveBegegnungErmoeglicht' => 'iBE',
          'programmpunkteGemeinsamDurchgefuehrt' => TRUE,
          'programmpunkteGemeinsamDurchgefuehrtFreitext' => '',
          'jugendlicheBeteiligt' => 'jB',
          'methoden' => 'm',
          'besondere' => 'b',
          'erschwerteZugangsvoraussetzungenBeteiligt' => 'eZB',
          'beurteilungTeilnehmer' => 'bT',
          'evaluierungsinstrumente' => 'e',
          'teilnahmenachweis' => TRUE,
          'schlussfolgerungen' => 's',
          'massnahmenGeplant' => 'mG',
          'veroeffentlichungen' => 'v',
          'hinweisBMFSFJ' => 'hB',
          'anregungenBMFSFJ' => 'aB',
        ],
        'dokumente' => $dokumente,
        'foerderung' => (object) [
          'teilnahmetage' => 1,
          'honorare' => 2,
          'fahrtkosten' => 3,
          'zuschlaege' => 4,
        ],
      ],
    ];

    static::assertValidationValid($validator->validate($data, $validationSchema));
    static::assertAllPropertiesSet($validationSchema, $data);

    $programmtage = 3;
    static::assertSame($programmtage, $data->reportData->grunddaten->programmtage);
    static::assertSame($teilnehmerGesamt, $data->reportData->teilnehmer->gesamt);
    static::assertSame($programmtage * $teilnehmerGesamt, $data->reportData->teilnehmer->teilnehmertage);

    static::assertSame(
      round($teilnehmerGesamt * $programmtage * 40, 2),
      $data->reportData->zuschuss->teilnehmerkostenMax,
    );
    static::assertSame(
      round($programmtageMitHonorar * 305, 2),
      $data->reportData->zuschuss->honorarkostenMax,
    );
    static::assertSame(
      round($teilnehmerDeutschlandMitFahrtkosten * $fahrtstreckeInKm * 0.08, 2),
      $data->reportData->zuschuss->fahrtkostenAuslandEuropaMax
    );
    static::assertSame(
      round($teilnehmerDeutschlandMitFahrtkosten * $fahrtstreckeInKm * 0.12, 2),
      $data->reportData->zuschuss->fahrtkostenNichtEuropaMax
    );
    static::assertSame(0, $data->reportData->zuschuss->fahrtkostenMax);
    static::assertSame(0, $data->reportData->zuschuss->zuschlagMax);
    static::assertSame(10, $data->reportData->foerderung->summe);
  }

  public function testValidationSpracheAndere(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $fahrtstreckeInKm = 100;
    $teilnehmerDeutschlandGesamt = 10;
    $teilnehmerDeutschlandMitFahrtkosten = 9;
    $teilnehmerPartnerlandGesamt = 11;

    $grunddaten = (object) [
      'titel' => 'Test',
      'kurzbeschreibungDesInhalts' => 'foo bar',
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
      'programmtageMitHonorar' => 0,
      'artDerMassnahme' => 'fachkraefteprogramm',
      'begegnungsland' => 'deutschland',
      'stadt' => 'Stadt',
      'land' => 'Land',
      'fahrtstreckeInKm' => $fahrtstreckeInKm,
    ];

    $teilnehmer = (object) [
      'deutschland' => (object) [
        'gesamt' => $teilnehmerDeutschlandGesamt,
        'weiblich' => 4,
        'divers' => 3,
        'unter27' => 2,
        'inJugendhilfeEhrenamtlichTaetig' => 1,
        'inJugendhilfeHauptamtlichTaetig' => 0,
        'referenten' => 5,
        'mitFahrtkosten' => $teilnehmerDeutschlandMitFahrtkosten,
      ],
      'partnerland' => (object) [
        'gesamt' => $teilnehmerPartnerlandGesamt,
        'weiblich' => 4,
        'divers' => 3,
        'unter27' => 2,
        'inJugendhilfeEhrenamtlichTaetig' => 1,
        'inJugendhilfeHauptamtlichTaetig' => 0,
        'referenten' => 5,
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

    $foerderung = (object) [
      'teilnahmetage' => 1,
      'honorare' => 2,
      'fahrtkosten' => 3,
      'zuschlaege' => 4,
    ];

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'teilnehmer' => $teilnehmer,
        'zuschuss' => (object) [],
        'sachbericht' => [
          'aenderungen' => '',
          'verstaendigungFreitext' => '',
        ],
        'dokumente' => $dokumente,
        'foerderung' => (object) [],
      ],
    ];
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'grunddaten' => $grunddaten,
        'teilnehmer' => $teilnehmer,
        'zuschuss' => (object) [],
        'sachbericht' => (object) [
          'durchgefuehrt' => 'geplant',
          'form' => 'praesenz',
          'aenderungen' => '',
          'sprache' => 'andere',
          'andereSprache' => '',
          'verstaendigungBewertung' => 'gut',
          'verstaendigungFreitext' => '',
          'sprachlicheUnterstuetzung' => FALSE,
          'sprachlicheUnterstuetzungArt' => '',
          'sprachlicheUnterstuetzungProgrammpunkte' => '',
          'sprachlicheUnterstuetzungErfahrungen' => '',
          'vorbereitung' => 'v',
          'vorbereitungstreffen' => TRUE,
          'vorbereitungstreffenFreitext' => '',
          'vorbereitungTeilnehmer' => 'vT',
          'themenfelder' => ['politik'],
          'zieleErreicht' => 'zE',
          'intensiveBegegnungErmoeglicht' => 'iBE',
          'programmpunkteGemeinsamDurchgefuehrt' => TRUE,
          'programmpunkteGemeinsamDurchgefuehrtFreitext' => '',
          'jugendlicheBeteiligt' => 'jB',
          'methoden' => 'm',
          'besondere' => 'b',
          'erschwerteZugangsvoraussetzungenBeteiligt' => 'eZB',
          'beurteilungTeilnehmer' => 'bT',
          'evaluierungsinstrumente' => 'e',
          'teilnahmenachweis' => TRUE,
          'schlussfolgerungen' => 's',
          'massnahmenGeplant' => 'mG',
          'veroeffentlichungen' => 'v',
          'hinweisBMFSFJ' => 'hB',
          'anregungenBMFSFJ' => 'aB',
        ],
        'dokumente' => $dokumente,
        'foerderung' => (object) [
          'teilnahmetage' => 1,
          'honorare' => 2,
          'fahrtkosten' => 3,
          'zuschlaege' => 4,
        ],
      ],
    ];
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data->reportData->sachbericht->andereSprache = 'aS';
    static::assertValidationValid($validator->validate($data, $validationSchema));
    static::assertAllPropertiesSet($validationSchema, $data);
  }

  public function testUiSchema(): void {
    static::assertScopesExist($this->form->getJsonSchema()->toStdClass(), $this->form->getUiSchema());
  }

}
