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

use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class IJBReportFormFactory implements ReportFormFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    // In draft report data fields may be empty.
    $reportDataDraftSchema = new JsonSchemaObject([
      'durchgefuehrt' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'geplant' => 'entsprechend dem geplanten Programm',
          'geaendert' => 'mit folgenden wesentlichen Änderungen (kurze Begründung für die Änderung):',
        ]),
      ]),
      'aenderungen' => new JsonSchemaString(),
      'sprache' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'partnersprache' => 'in der Partnersprache',
          'deutsch' => 'auf Deutsch',
          'andere' => 'auf:',
        ]),
      ]),
      'andereSprache' => new JsonSchemaString(['maxLength' => 50]),

      // Abschnitt 1: Sprachliche Verständigung
      'verstaendigungBewertung' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'gut' => 'gut',
          'zufriedenstellend' => 'zufriedenstellend',
          'schlecht' => 'schlecht (bitte Begründung angeben)',
        ]),
      ]),
      'verstaendigungFreitext' => new JsonSchemaString(),
      'sprachlicheUnterstuetzung' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2(['ja' => TRUE, 'nein' => FALSE]),
      ]),
      'sprachlicheUnterstuetzungArt' => new JsonSchemaString(),
      'sprachlicheUnterstuetzungProgrammpunkte' => new JsonSchemaString(),
      'sprachlicheUnterstuetzungErfahrungen' => new JsonSchemaString(),

      // Abschnitt 2: Vorbereitung der Maßnahme
      'vorbereitung' => new JsonSchemaString(),
      'vorbereitungstreffen' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2(['ja' => TRUE, 'nein, weil' => FALSE]),
      ]),
      'vorbereitungstreffenFreitext' => new JsonSchemaString(),
      'vorbereitungTeilnehmer' => new JsonSchemaString(),

      // Abschnitt 3: Durchführung/Inhalt/Methoden
      'themenfelder' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'kennenlernen' => 'gegenseitiges Kennenlernen',
          'politik' => 'Politik und Gesellschaft',
          'medien' => 'Medien',
          'alltag' => 'Alltag in Familie u. Heimatort',
          'geschichte' => 'Geschichte',
          'gewaltpraeventation' => 'Gewaltprävention',
          'sitten' => 'Sitten und Gebräuche',
          'sport' => 'Sport',
          'didaktik' => 'Didaktik und Methodik',
          'religion' => 'Religion',
          'natur' => 'Natur und Umwelt',
          'spiel' => 'Spiel und Spielen',
          'aufwachsen' => 'Gesundes Aufwachsen',
          'kunst' => 'Kunst und Kultur',
          'fortbildung' => 'Teamer-, Leiterfortbildung',
          'ausbildung' => 'Schule, Ausbildung, Beruf',
          'rettungsdienste' => 'Rettungs- und Hilfsdienste',
          'sonstige' => 'Sonstige',
          'geschlechtlicheIdentitaet' => 'geschlechtliche Identität',
          'technik' => 'Technik und Handwerk',
          'ohne' => 'ohne Schwerpunktthema',
        ]),
      ]), ['maxItems' => 3, 'uniqueItems' => TRUE]),
      'zieleErreicht' => new JsonSchemaString(),
      'intensiveBegegnungErmoeglicht' => new JsonSchemaString(),
      'programmpunkteGemeinsamDurchgefuehrt' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2(['ja:' => TRUE, 'nein,' => FALSE]),
      ]),
      'programmpunkteGemeinsamDurchgefuehrtFreitext' => new JsonSchemaString(),
      'jugendlicheBeteiligt' => new JsonSchemaString(),
      'methoden' => new JsonSchemaString(),
      'besondere' => new JsonSchemaString(),
      'erschwerteZugangsvoraussetzungenBeteiligt' => new JsonSchemaString(),

      // Abschnitt 4: Auswertung, Evaluierung und Perspektiven
      'beurteilungTeilnehmer' => new JsonSchemaString(),
      'evaluierungsinstrumente' => new JsonSchemaString(),
      'teilnahmenachweis' => new JsonSchemaBoolean([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2(['ja' => TRUE, 'nein' => FALSE]),
      ]),
      'schlussfolgerungen' => new JsonSchemaString(),
      'massnahmenGeplant' => new JsonSchemaString(),
      'veroeffentlichungen' => new JsonSchemaString(),
      'hinweisBMFSFJ' => new JsonSchemaString(),
      'anregungenBMFSFJ' => new JsonSchemaString(),
    ]);

    $reportDataSchema = $reportDataDraftSchema->clone();
    $requiredStrings = [
      'durchgefuehrt',
      'sprache',
      'verstaendigungBewertung',
      'verstaendigungFreitext',
      'vorbereitung',
      'vorbereitungstreffenFreitext',
      'vorbereitungTeilnehmer',
      'zieleErreicht',
      'intensiveBegegnungErmoeglicht',
      'programmpunkteGemeinsamDurchgefuehrt',
      'programmpunkteGemeinsamDurchgefuehrtFreitext',
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
    ];

    $reportDataSchema['required'] = array_merge($requiredStrings, $requiredArrays, $requiredBooleans);
    foreach ($requiredStrings as $property) {
      // @phpstan-ignore-next-line
      $reportDataSchema['properties'][$property]['minLength'] ??= 1;
    }
    foreach ($requiredArrays as $property) {
      // @phpstan-ignore-next-line
      $reportDataSchema['properties'][$property]['minItems'] ??= 1;
    }

    $validations = $reportDataSchema['properties']['aenderungen']['$validations'] ?? [];
    $validations[] = JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || durchgefuehrt === "geplant"',
        'variables' => [
          'durchgefuehrt' => new JsonSchemaDataPointer('1/durchgefuehrt'),
        ],
      ],
      'message' => 'Bitte Begründung für die Änderungen angeben.',
    ]);
    // @phpstan-ignore-next-line
    $reportDataSchema['properties']['aenderungen']['$validations'] = $validations;

    $validations = $reportDataSchema['properties']['andereSprache']['$validations'] ?? [];
    $validations[] = JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || sprache !== "andere"',
        'variables' => [
          'sprache' => new JsonSchemaDataPointer('1/sprache'),
        ],
      ],
      'message' => 'Bitte die Verständigungssprache angeben.',
    ]);
    // @phpstan-ignore-next-line
    $reportDataSchema['properties']['andereSprache']['$validations'] = $validations;

    $validations = $reportDataSchema['properties']['sprachlicheUnterstuetzungArt']['$validations'] ?? [];
    $validations[] = JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || !sprachlicheUnterstuetzung',
        'variables' => [
          'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
        ],
      ],
      'message' => 'Bitte die Art der Unterstützung angeben.',
    ]);
    // @phpstan-ignore-next-line
    $reportDataSchema['properties']['sprachlicheUnterstuetzungArt']['$validations'] = $validations;

    $validations = $reportDataSchema['properties']['sprachlicheUnterstuetzungProgrammpunkte']['$validations'] ?? [];
    $validations[] = JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || !sprachlicheUnterstuetzung',
        'variables' => [
          'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
        ],
      ],
      'message' => 'Bitte die Programmpunkte angeben.',
    ]);
    // @phpstan-ignore-next-line
    $reportDataSchema['properties']['sprachlicheUnterstuetzungProgrammpunkte']['$validations'] = $validations;

    $validations = $reportDataSchema['properties']['sprachlicheUnterstuetzungErfahrungen']['$validations'] ?? [];
    $validations[] = JsonSchema::fromArray([
      'keyword' => 'evaluate',
      'value' => [
        'expression' => 'data != "" || !sprachlicheUnterstuetzung',
        'variables' => [
          'sprachlicheUnterstuetzung' => new JsonSchemaDataPointer('1/sprachlicheUnterstuetzung'),
        ],
      ],
      'message' => 'Bitte die Erfahrungen angeben.',
    ]);
    // @phpstan-ignore-next-line
    $reportDataSchema['properties']['sprachlicheUnterstuetzungErfahrungen']['$validations'] = $validations;

    $jsonSchema = new JsonSchemaObject([
      'reportData' => $reportDataDraftSchema,
    ], [
      'if' => JsonSchema::fromArray([
        'properties' => [
          '_action' => ['not' => ['const' => 'save']],
        ],
      ]),
      'then' => new JsonSchemaObject([
        'reportData' => $reportDataSchema,
      ]),
    ]);

    $scopePrefix = '#/properties/reportData/properties';
    $uiSchema = new JsonFormsGroup('Sachbericht', [
      new JsonFormsGroup('Infotext', [], <<<'EOD'
Mit der Beantwortung der nachfolgenden Fragen gebt Ihr uns und dem BMFSFJ die
Möglichkeit, einen Einblick in Eure Maßnahme zu gewinnen. Eure Erfahrungen
helfen uns, den internationalen Jugendaustausch weiterzuentwickeln, bewährte
Methoden oder Programmteile weiterzuempfehlen sowie möglicherweise häufiger
vorkommende Herausforderungen zu erkennen und bei der Behebung zu helfen.
Vor diesem Hintergrund bitten wir Euch darum, die Fragen aufmerksam zu
beantworten. Nutzt dafür gerne so viel Platz, wie Ihr benötigt.
EOD
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/durchgefuehrt',
        'Die Maßnahme wurde durchgeführt',
        NULL,
        ['format' => 'radio']
      ),
      new JsonFormsControl('#/properties/reportData/properties/aenderungen', '', NULL, ['multi' => TRUE], [
        'rule' => new JsonFormsRule(
          'ENABLE',
          '#/properties/reportData/properties/durchgefuehrt',
          JsonSchema::fromArray(['const' => 'geaendert'])
        ),
      ]),

      new JsonFormsGroup('1. Sprachliche Verständigung', [
        new JsonFormsControl(
          '#/properties/reportData/properties/sprache',
          '1.1 Die sprachliche Verständigung während der Maßnahme erfolgte:',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl('#/properties/reportData/properties/andereSprache', '', NULL, NULL, [
          'rule' => new JsonFormsRule(
            'ENABLE',
            '#/properties/reportData/properties/sprache',
            JsonSchema::fromArray(['const' => 'andere'])
          ),
        ]),
        new JsonFormsControl(
          '#/properties/reportData/properties/verstaendigungBewertung',
          '1.2 Die sprachliche Verständigung während der Maßnahme war:',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl(
          '#/properties/reportData/properties/verstaendigungFreitext',
          'Anmerkungen',
          NULL,
          ['multi' => TRUE]
        ),
      ]),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzung',
        '1.3 Wurde während der Maßnahme sprachliche Unterstützung ' .
        '(Sprachanimation, Sprachmittlung, Dolmetschung) in Anspruch genommen?',
        NULL,
        ['format' => 'radio']
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzungArt',
        'Art der Unterstützung',
        NULL,
        [],
        [
          'rule' => new JsonFormsRule(
            'HIDE',
            '#/properties/reportData/properties/sprachlicheUnterstuetzung',
            JsonSchema::fromArray(['const' => FALSE])
          ),
        ]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzungProgrammpunkte',
        'Bei welchen Programmpunkten?',
        NULL,
        [],
        [
          'rule' => new JsonFormsRule(
            'HIDE',
            '#/properties/reportData/properties/sprachlicheUnterstuetzung',
            JsonSchema::fromArray(['const' => FALSE])
          ),
        ]
      ),
      new JsonFormsControl(
        '#/properties/reportData/properties/sprachlicheUnterstuetzungErfahrungen',
        'Welche Erfahrungen habt Ihr damit gemacht?',
        NULL,
        [],
        [
          'rule' => new JsonFormsRule(
            'HIDE',
            '#/properties/reportData/properties/sprachlicheUnterstuetzung',
            JsonSchema::fromArray(['const' => FALSE])
          ),
        ]
      ),

      new JsonFormsGroup('2. Vorbereitung der Maßnahme', [
        new JsonFormsControl(
          '#/properties/reportData/properties/vorbereitung',
          '2.1 Über welche Erfahrungen verfügte(n) die Leitungsperson(en) und wie erfolgte die Vorbereitung?',
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/vorbereitungstreffen",
          'Gab es ein Vorbereitungstreffen?',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl("$scopePrefix/vorbereitungstreffenFreitext", ''),
        new JsonFormsControl(
          "$scopePrefix/vorbereitungTeilnehmer",
          '2.2 Wie bereiteten sich die Teilnehmenden auf die Maßnahme vor?',
          NULL,
          ['multi' => TRUE]
        ),
      ]),

      new JsonFormsGroup('3. Durchführung/Inhalt/Methoden', [
        new JsonFormsControl("$scopePrefix/themenfelder",
          <<<'EOD'
3.1 Welche inhaltlichen Ziele wurden/werden (kurz und ggf. mittel- bis
langfristig) mit der Maßnahme verfolgt (siehe auch Themenfelder im
Formblatt M Statistische Mitteilungen)?<br>Themenfelder (bis zu 3 Themen können
angekreuzt werden)
EOD),
        new JsonFormsControl(
          "$scopePrefix/zieleErreicht",
          'Welche dieser Ziele wurden aus Eurer Sicht erreicht?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          "$scopePrefix/intensiveBegegnungErmoeglicht",
          '3.2 Wie wurde eine intensive Begegnung der Teilnehmenden ermöglicht?',
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/programmpunkteGemeinsamDurchgefuehrt",
          <<<'EOD'
Wurden alle Programmpunkte gemeinsam von den deutschen und ausländischen
Jugendlichen der Partnerorganisation durchgeführt? Wenn nein, erläutert bitte,
welche Punkte nicht gemeinsam verbracht worden sind und warum.
EOD,
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl(
          "$scopePrefix/programmpunkteGemeinsamDurchgefuehrtFreitext",
          '',
          NULL,
          ['multi' => TRUE],
        ),

        new JsonFormsControl(
          "$scopePrefix/jugendlicheBeteiligt",
          <<<'EOD'
3.3 Bei Jugendbegegnungen: In welcher Form waren die Jugendlichen an der
Vorbereitung, Durchführung sowie Auswertung des Projekts beteiligt?
EOD,
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/methoden",
          <<<'EOD'
3.4 Mit welchen Methoden und Programmbausteinen wurde im Projekt gearbeitet?
Welche haben sich bewährt, welche nicht und warum?
EOD,
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/besondere",
          <<<'EOD'
3.5 Was war das Besondere an der Begegnung? Gab es aus Sicht der
Begegnungsleitung ein Highlight oder herausragende Erlebnisse?
EOD,
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/erschwerteZugangsvoraussetzungenBeteiligt",
          <<<'EOD'
3.6 Waren junge Menschen mit erschwerten Zugangsvoraussetzungen an der Maßnahme
beteiligt (z.B. Jugendliche mit Migrationsgeschichte, Fluchterfahrung,
Beeinträchtigung oder erhöhtem Betreuungsbedarf)? Wenn ja, welche Erfahrungen
habt Ihr dabei gemacht?
EOD,
          NULL,
          ['multi' => TRUE],
        ),
      ]),

      new JsonFormsGroup('4. Auswertung, Evaluierung und Perspektiven', [
        new JsonFormsControl(
          "$scopePrefix/beurteilungTeilnehmer",
          '4.1 Wie beurteilten die Teilnehmenden die Maßnahme?',
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/evaluierungsinstrumente",
          '4.2 Welche Evaluierungsinstrumente wurden genutzt?',
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/teilnahmenachweis",
          <<<'EOD'
4.4 Stellt Ihr Euren Teilnehmenden einen „Teilnahmenachweis International“ aus
(vgl. <a href="https://www.nachweise-international.de/" target="_blank">www.nachweise-international.de</a>)?
EOD,
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl(
          "$scopePrefix/schlussfolgerungen",
          <<<'EOD'
4.5 Welche Schlussfolgerungen zieht die Leitung aus der Maßnahme? Wie werden die
Erfahrungen durch die Leitung ausgewertet und weitergegeben?
EOD,
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/massnahmenGeplant",
          '4.6 Sind weitere Maßnahmen geplant? Wenn ja, welche?',
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/veroeffentlichungen",
          <<<'EOD'
4.7 Welche Veröffentlichungen oder Produkte gab es? Bitte ggf. einen Link zum
Artikel auf der Homepage angeben, Kopie(n) von Pressemitteilung(en),
Belegexemplare etc. beifügen.
EOD,
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/hinweisBMFSFJ",
          '4.8 Wie wurde auf die Förderung durch das BMFSFJ hingewiesen?',
          NULL,
          ['multi' => TRUE],
        ),
        new JsonFormsControl(
          "$scopePrefix/anregungenBMFSFJ",
          '4.9 Welche Anregungen für den Bundearbeitskreis/das BMFSFJ haben sich ggf. aus der Maßnahme ergeben?',
          NULL,
          ['multi' => TRUE],
        ),
      ]),
    ]);

    return new JsonFormsForm($jsonSchema, $uiSchema);
  }

}
