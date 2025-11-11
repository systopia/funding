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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Report\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class IJBSachberichtJsonSchema extends JsonSchemaObject {

  public const THEMENFELDER_OPTIONS = [
    'Politik und Gesellschaft' => 'politik',
    'Medien' => 'medien',
    'Gewaltprävention' => 'gewaltpraeventation',
    'Sport' => 'sport',
    'Didaktik und Methodik' => 'didaktik',
    'Religion' => 'religion',
    'Natur und Umwelt' => 'natur',
    'Spiel und Spielen' => 'spiel',
    'Gesundes Aufwachsen' => 'aufwachsen',
    'Kunst und Kultur' => 'kunst',
    'Teamer-, Leiterfortbildung' => 'fortbildung',
    'Schule, Ausbildung, Beruf' => 'ausbildung',
    'Rettungs- und Hilfsdienste' => 'rettungsdienste',
    'Sonstige' => 'sonstige',
    'geschlechtliche Identität' => 'geschlechtlicheIdentitaet',
    'Technik und Handwerk' => 'technik',
    'ohne Schwerpunktthema' => 'ohne',
  ];

  public function __construct() {
    parent::__construct([
      'durchgefuehrt' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'entsprechend dem geplanten Programm' => 'geplant',
          'mit folgenden wesentlichen Änderungen (kurze Begründung für die Änderung):' => 'geaendert',
        ]),
      ]),
      'form' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'in Präsenz' => 'praesenz',
          'digital/hybrid' => 'digital_hybrid',
          'in Präsenz mit digitalen Anteilen' => 'digitaleAnteile',
        ]),
      ]),
      'aenderungen' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || durchgefuehrt === "geplant"',
              'variables' => [
                'durchgefuehrt' => new JsonSchemaDataPointer('1/durchgefuehrt'),
              ],
            ],
            'message' => 'Bitte Begründung für die Änderungen angeben.',
          ]),
        ],
      ], TRUE),
      'sprache' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'in der Partnersprache' => 'partnersprache',
          'auf Deutsch' => 'deutsch',
          'auf:' => 'andere',
        ]),
      ]),
      'andereSprache' => new JsonSchemaString([
        'maxLength' => 50,
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || sprache !== "andere"',
              'variables' => [
                'sprache' => new JsonSchemaDataPointer('1/sprache'),
              ],
            ],
            'message' => 'Bitte die Verständigungssprache angeben.',
          ]),
        ],
      ], TRUE),

      // Abschnitt 1: Sprachliche Verständigung
      'verstaendigungBewertung' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'gut' => 'gut',
          'zufriedenstellend' => 'zufriedenstellend',
          'schlecht (bitte Begründung angeben)' => 'schlecht',
        ]),
      ]),
      'verstaendigungFreitext' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || verstaendigungBewertung !== "schlecht"',
              'variables' => [
                'verstaendigungBewertung' => new JsonSchemaDataPointer('1/verstaendigungBewertung'),
              ],
            ],
            'message' => 'Bitte eine Begründung angeben.',
          ]),
        ],
      ]),
      'sprachlicheUnterstuetzung' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'ja' => TRUE,
          'nein' => FALSE,
        ]),
      ]),
      'sprachlicheUnterstuetzungArt' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || !sprachlicheUnterstuetzung',
              'variables' => [
                'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
              ],
            ],
            'message' => 'Bitte die Art der Unterstützung angeben.',
          ]),
        ],
      ], TRUE),
      'sprachlicheUnterstuetzungProgrammpunkte' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || !sprachlicheUnterstuetzung',
              'variables' => [
                'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
              ],
            ],
            'message' => 'Bitte die Programmpunkte angeben.',
          ]),
        ],
      ], TRUE),
      'sprachlicheUnterstuetzungErfahrungen' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || !sprachlicheUnterstuetzung',
              'variables' => [
                'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
              ],
            ],
            'message' => 'Bitte die Erfahrungen angeben.',
          ]),
        ],
      ], TRUE),

      // Abschnitt 2: Vorbereitung der Maßnahme
      'vorbereitung' => new JsonSchemaString(['minLength' => 1]),
      'vorbereitungstreffen' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'ja' => TRUE,
          'nein, weil' => FALSE,
        ]),
      ]),
      'vorbereitungstreffenFreitext' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || vorbereitungstreffen === TRUE',
              'variables' => [
                'vorbereitungstreffen' => new JsonSchemaDataPointer('1/vorbereitungstreffen'),
              ],
            ],
            'message' => 'Bitte eine Begründung angeben.',
          ]),
        ],
      ]),
      'vorbereitungTeilnehmer' => new JsonSchemaString(['minLength' => 1]),

      // Abschnitt 3: Durchführung/Inhalt/Methoden
      'themenfelder' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2(self::THEMENFELDER_OPTIONS),
      ]), ['maxItems' => 3, 'uniqueItems' => TRUE]),
      'zieleErreicht' => new JsonSchemaString(['minLength' => 1]),
      'intensiveBegegnungErmoeglicht' => new JsonSchemaString(['minLength' => 1]),
      'programmpunkteGemeinsamDurchgefuehrt' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'ja:' => TRUE,
          'nein,' => FALSE,
        ]),
      ]),
      'programmpunkteGemeinsamDurchgefuehrtFreitext' => new JsonSchemaString([
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || programmpunkteGemeinsamDurchgefuehrt === TRUE',
              'variables' => [
                'programmpunkteGemeinsamDurchgefuehrt'
                => new JsonSchemaDataPointer('1/programmpunkteGemeinsamDurchgefuehrt'),
              ],
            ],
            'message' => 'Bitte eine Begründung angeben.',
          ]),
        ],
      ]),
      'jugendlicheBeteiligt' => new JsonSchemaString(['minLength' => 1]),
      'methoden' => new JsonSchemaString(['minLength' => 1]),
      'besondere' => new JsonSchemaString(['minLength' => 1]),
      'erschwerteZugangsvoraussetzungenBeteiligt' => new JsonSchemaString(['minLength' => 1]),

      // Abschnitt 4: Auswertung, Evaluierung und Perspektiven
      'beurteilungTeilnehmer' => new JsonSchemaString(['minLength' => 1]),
      'evaluierungsinstrumente' => new JsonSchemaString(['minLength' => 1]),
      'teilnahmenachweis' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          'ja' => TRUE,
          'nein' => FALSE,
        ]),
      ]),
      'schlussfolgerungen' => new JsonSchemaString(['minLength' => 1]),
      'massnahmenGeplant' => new JsonSchemaString(['minLength' => 1]),
      'veroeffentlichungen' => new JsonSchemaString(['minLength' => 1]),
      'hinweisBMFSFJ' => new JsonSchemaString(['minLength' => 1]),
      'anregungenBMFSFJ' => new JsonSchemaString(['minLength' => 1]),
    ], [
      'required' => [
        'durchgefuehrt',
        'form',
        'sprache',
        'verstaendigungBewertung',
        'vorbereitung',
        'vorbereitungTeilnehmer',
        'zieleErreicht',
        'intensiveBegegnungErmoeglicht',
        'jugendlicheBeteiligt',
        'methoden',
        'besondere',
        'erschwerteZugangsvoraussetzungenBeteiligt',
        'beurteilungTeilnehmer',
        'evaluierungsinstrumente',
        'schlussfolgerungen',
        'massnahmenGeplant',
        'veroeffentlichungen',
        'hinweisBMFSFJ',
        'anregungenBMFSFJ',
        'themenfelder',
        'sprachlicheUnterstuetzung',
        'sprachlicheUnterstuetzungArt',
        'sprachlicheUnterstuetzungProgrammpunkte',
        'sprachlicheUnterstuetzungErfahrungen',
        'vorbereitungstreffen',
        'teilnahmenachweis',
        'programmpunkteGemeinsamDurchgefuehrt',
      ],
    ]);
  }

}
