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
        'aenderungen' => '',
        'verstaendigungFreitext' => '',
      ],
    ];
    static::assertValidationValid($validator->validate($data, $validationSchema));
  }

  public function testValidation(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'aenderungen' => '',
        'verstaendigungFreitext' => '',
      ],
    ];
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'durchgefuehrt' => 'geplant',
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
        'themenfelder' => ['kennenlernen'],
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
        'veroeffentlichungenDateien' => [
          'https://example.org/test.txt',
        ],
        'hinweisBMFSFJ' => 'hB',
        'anregungenBMFSFJ' => 'aB',
      ],
    ];
    static::assertAllPropertiesSet($validationSchema, $data);
    static::assertValidationValid($validator->validate($data, $validationSchema));
  }

  public function testValidationSpracheAndere(): void {
    $validationSchema = $this->form->getJsonSchema()->toStdClass();
    $validator = OpisValidatorFactory::getValidator();

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'aenderungen' => '',
        'verstaendigungFreitext' => '',
      ],
    ];
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data = (object) [
      '_action' => 'some-action',
      'reportData' => (object) [
        'durchgefuehrt' => 'geplant',
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
        'themenfelder' => ['kennenlernen'],
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
        'veroeffentlichungenDateien' => [
          'https://example.org/test.txt',
        ],
        'hinweisBMFSFJ' => 'hB',
        'anregungenBMFSFJ' => 'aB',
      ],
    ];
    static::assertAllPropertiesSet($validationSchema, $data);
    static::assertFalse($validator->validate($data, $validationSchema)->isValid());

    $data->reportData->andereSprache = 'aS';
    static::assertValidationValid($validator->validate($data, $validationSchema));
  }

  public function testUiSchema(): void {
    static::assertScopesExist($this->form->getJsonSchema()->toStdClass(), $this->form->getUiSchema());
  }

}
