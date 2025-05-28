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
    '' => NULL,
    'Politik und Gesellschaft' => 'politik',
    'Medien' => 'medien',
    'Geschichte' => 'geschichte',
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
          '' => NULL,
          'entsprechend dem geplanten Programm' => 'geplant',
          'mit folgenden wesentlichen Änderungen (kurze Begründung für die Änderung):' => 'geaendert',
        ]),
      ], TRUE),
      'form' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'in Präsenz' => 'praesenz',
          'digital/hybrid' => 'digital_hybrid',
          'in Präsenz mit digitalen Anteilen' => 'digitaleAnteile',
        ]),
      ], TRUE),
      'aenderungen' => new JsonSchemaString([], TRUE),
      'sprache' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'in der Partnersprache' => 'partnersprache',
          'auf Deutsch' => 'deutsch',
          'auf:' => 'andere',
        ]),
      ], TRUE),
      'andereSprache' => new JsonSchemaString(['maxLength' => 50], TRUE),

      // Abschnitt 1: Sprachliche Verständigung
      'verstaendigungBewertung' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'gut' => 'gut',
          'zufriedenstellend' => 'zufriedenstellend',
          'schlecht (bitte Begründung angeben)' => 'schlecht',
        ]),
      ], TRUE),
      'verstaendigungFreitext' => new JsonSchemaString(),
      'sprachlicheUnterstuetzung' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'ja' => TRUE,
          'nein' => FALSE,
        ]),
      ], TRUE),
      'sprachlicheUnterstuetzungArt' => new JsonSchemaString(),
      'sprachlicheUnterstuetzungProgrammpunkte' => new JsonSchemaString(),
      'sprachlicheUnterstuetzungErfahrungen' => new JsonSchemaString(),

      // Abschnitt 2: Vorbereitung der Maßnahme
      'vorbereitung' => new JsonSchemaString(),
      'vorbereitungstreffen' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'ja' => TRUE,
          'nein, weil' => FALSE,
        ]),
      ], TRUE),
      'vorbereitungstreffenFreitext' => new JsonSchemaString(),
      'vorbereitungTeilnehmer' => new JsonSchemaString(),

      // Abschnitt 3: Durchführung/Inhalt/Methoden
      'themenfelder' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2(self::THEMENFELDER_OPTIONS),
      ]), ['maxItems' => 3, 'uniqueItems' => TRUE]),
      'zieleErreicht' => new JsonSchemaString(),
      'intensiveBegegnungErmoeglicht' => new JsonSchemaString(),
      'programmpunkteGemeinsamDurchgefuehrt' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'ja:' => TRUE,
          'nein,' => FALSE,
        ]),
      ], TRUE),
      'programmpunkteGemeinsamDurchgefuehrtFreitext' => new JsonSchemaString(),
      'jugendlicheBeteiligt' => new JsonSchemaString(),
      'methoden' => new JsonSchemaString(),
      'besondere' => new JsonSchemaString(),
      'erschwerteZugangsvoraussetzungenBeteiligt' => new JsonSchemaString(),

      // Abschnitt 4: Auswertung, Evaluierung und Perspektiven
      'beurteilungTeilnehmer' => new JsonSchemaString(),
      'evaluierungsinstrumente' => new JsonSchemaString(),
      'teilnahmenachweis' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '' => NULL,
          'ja' => TRUE,
          'nein' => FALSE,
        ]),
      ], TRUE),
      'schlussfolgerungen' => new JsonSchemaString(),
      'massnahmenGeplant' => new JsonSchemaString(),
      'veroeffentlichungen' => new JsonSchemaString(),
      'hinweisBMFSFJ' => new JsonSchemaString(),
      'anregungenBMFSFJ' => new JsonSchemaString(),
    ]);
  }

  public function withValidations(): self {
    $schema = clone $this;
    $schema->addValidations();

    return $schema;
  }

  private function addValidations(): void {
    $requiredStrings = [
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
    ];

    $requiredArrays = [
      'themenfelder',
    ];

    $requiredBooleans = [
      'sprachlicheUnterstuetzung',
      'vorbereitungstreffen',
      'teilnahmenachweis',
      'programmpunkteGemeinsamDurchgefuehrt',
    ];

    $this['required'] = array_merge($requiredStrings, $requiredArrays, $requiredBooleans);

    foreach ($requiredStrings as $property) {
      // @phpstan-ignore-next-line
      $this['properties'][$property]['type'] = 'string';
      // @phpstan-ignore-next-line
      $this['properties'][$property]['minLength'] ??= 1;
      if (isset($this['properties'][$property]['oneOf'])) {
        $this['properties'][$property]['oneOf'] = array_values(array_filter(
          $this['properties'][$property]['oneOf'],
          fn ($entry) => $entry['const'] !== NULL
        ));
      }
    }

    foreach ($requiredArrays as $property) {
      // @phpstan-ignore-next-line
      $this['properties'][$property]['minItems'] ??= 1;
    }

    foreach ($requiredBooleans as $property) {
      // @phpstan-ignore-next-line
      $this['properties'][$property]['type'] = 'boolean';
      if (isset($this['properties'][$property]['oneOf'])) {
        $this['properties'][$property]['oneOf'] = array_values(array_filter(
          $this['properties'][$property]['oneOf'],
          fn ($entry) => $entry['const'] !== NULL
        ));
      }
    }

    self::addValidation($this, 'aenderungen', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || durchgefuehrt === "geplant"',
        'variables' => [
          'durchgefuehrt' => new JsonSchemaDataPointer('1/durchgefuehrt'),
        ],
      ],
      'message' => 'Bitte Begründung für die Änderungen angeben.',
    ]));

    self::addValidation($this, 'andereSprache', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || sprache !== "andere"',
        'variables' => [
          'sprache' => new JsonSchemaDataPointer('1/sprache'),
        ],
      ],
      'message' => 'Bitte die Verständigungssprache angeben.',
    ]));

    self::addValidation($this, 'sprachlicheUnterstuetzungArt', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || !sprachlicheUnterstuetzung',
        'variables' => [
          'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
        ],
      ],
      'message' => 'Bitte die Art der Unterstützung angeben.',
    ]));

    self::addValidation($this, 'sprachlicheUnterstuetzungProgrammpunkte', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || !sprachlicheUnterstuetzung',
        'variables' => [
          'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
        ],
      ],
      'message' => 'Bitte die Programmpunkte angeben.',
    ]));

    self::addValidation($this, 'sprachlicheUnterstuetzungErfahrungen', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || !sprachlicheUnterstuetzung',
        'variables' => [
          'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
        ],
      ],
      'message' => 'Bitte die Erfahrungen angeben.',
    ]));

    self::addValidation($this, 'verstaendigungFreitext', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || verstaendigungBewertung !== "schlecht"',
        'variables' => [
          'verstaendigungBewertung' => new JsonSchemaDataPointer('1/verstaendigungBewertung'),
        ],
      ],
      'message' => 'Bitte eine Begründung angeben.',
    ]));

    self::addValidation($this, 'vorbereitungstreffenFreitext', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || vorbereitungstreffen === TRUE',
        'variables' => [
          'vorbereitungstreffen' => new JsonSchemaDataPointer('1/vorbereitungstreffen'),
        ],
      ],
      'message' => 'Bitte eine Begründung angeben.',
    ]));

    self::addValidation($this, 'programmpunkteGemeinsamDurchgefuehrtFreitext', JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || programmpunkteGemeinsamDurchgefuehrt === TRUE',
        'variables' => [
          'programmpunkteGemeinsamDurchgefuehrt' => new JsonSchemaDataPointer('1/programmpunkteGemeinsamDurchgefuehrt'),
        ],
      ],
      'message' => 'Bitte eine Begründung angeben.',
    ]));
  }

  private static function addValidation(self $schema, string $property, JsonSchema $validation): void {
    $validations = $schema['properties'][$property]['$validations'] ?? [];
    $validations[] = $validation;
    // @phpstan-ignore-next-line
    $schema['properties'][$property]['$validations'] = $validations;
  }

}
