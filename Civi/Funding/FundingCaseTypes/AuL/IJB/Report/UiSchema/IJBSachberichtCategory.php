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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Report\UiSchema;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class IJBSachberichtCategory extends JsonFormsCategory {

  public function __construct(string $scopePrefix) {
    parent::__construct('Sachbericht', [
      new JsonFormsGroup('Sachbericht', [], <<<'EOD'
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
        "$scopePrefix/durchgefuehrt",
        'Die Maßnahme wurde durchgeführt',
        NULL,
        ['format' => 'radio']
      ),
      new JsonFormsControl(
        "$scopePrefix/aenderungen", 'Änderungen und Begründung', NULL, ['multi' => TRUE], [
          'rule' => new JsonFormsRule(
            'ENABLE', "$scopePrefix/durchgefuehrt", JsonSchema::fromArray(['const' => 'geaendert'])
          ),
        ]
      ),
      new JsonFormsControl(
        "$scopePrefix/form",
        'Form der Durchführung der Maßnahme',
        NULL,
        ['format' => 'radio']
      ),

      new JsonFormsGroup('1. Sprachliche Verständigung', [
        new JsonFormsControl(
          "$scopePrefix/sprache",
          '1.1 Die sprachliche Verständigung während der Maßnahme erfolgte:',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl("$scopePrefix/andereSprache", 'Andere Sprache', NULL, NULL, [
          'rule' => new JsonFormsRule(
            'ENABLE',
            "$scopePrefix/sprache",
            JsonSchema::fromArray(['const' => 'andere'])
          ),
        ]),
        new JsonFormsControl(
          "$scopePrefix/verstaendigungBewertung",
          '1.2 Die sprachliche Verständigung während der Maßnahme war:',
          NULL,
          ['format' => 'radio'],
        ),
        new JsonFormsControl("$scopePrefix/verstaendigungFreitext", 'Anmerkungen', NULL, ['multi' => TRUE]),
        new JsonFormsControl(
          "$scopePrefix/sprachlicheUnterstuetzung",
          '1.3 Wurde während der Maßnahme sprachliche Unterstützung ' .
          '(Sprachanimation, Sprachmittlung, Dolmetschung) in Anspruch genommen?',
          NULL,
          ['format' => 'radio']
        ),
        new JsonFormsControl(
          "$scopePrefix/sprachlicheUnterstuetzungArt",
          'Art der Unterstützung',
          NULL,
          [],
          [
            'rule' => new JsonFormsRule(
              'SHOW',
              "$scopePrefix/sprachlicheUnterstuetzung",
              JsonSchema::fromArray(['const' => TRUE])
            ),
          ]
        ),
        new JsonFormsControl(
          "$scopePrefix/sprachlicheUnterstuetzungProgrammpunkte",
          'Bei welchen Programmpunkten?',
          NULL,
          [],
          [
            'rule' => new JsonFormsRule(
              'SHOW',
              "$scopePrefix/sprachlicheUnterstuetzung",
              JsonSchema::fromArray(['const' => TRUE])
            ),
          ]
        ),
        new JsonFormsControl(
          "$scopePrefix/sprachlicheUnterstuetzungErfahrungen",
          'Welche Erfahrungen habt Ihr damit gemacht?',
          NULL,
          [],
          [
            'rule' => new JsonFormsRule(
              'SHOW',
              "$scopePrefix/sprachlicheUnterstuetzung",
              JsonSchema::fromArray(['const' => TRUE])
            ),
          ]
        ),
      ]),

      new JsonFormsGroup('2. Vorbereitung der Maßnahme', [
        new JsonFormsControl(
          "$scopePrefix/vorbereitung",
          '2.1 Über welche Erfahrungen verfügte(n) die Leitungsperson(en) und wie erfolgte die Vorbereitung?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          "$scopePrefix/vorbereitungstreffen",
          'Gab es ein Vorbereitungstreffen?',
          NULL,
          ['format' => 'radio']
        ),
        new JsonFormsControl("$scopePrefix/vorbereitungstreffenFreitext", 'Anmerkungen'),
        new JsonFormsControl(
          "$scopePrefix/vorbereitungTeilnehmer",
          '2.2 Wie bereiteten sich die Teilnehmenden auf die Maßnahme vor?',
          NULL,
          ['multi' => TRUE]
        ),
      ]),

      new JsonFormsGroup('3. Durchführung/Inhalt/Methoden', [
        new JsonFormsControl("$scopePrefix/themenfelder", <<<'EOD'
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
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          "$scopePrefix/programmpunkteGemeinsamDurchgefuehrt",
          <<<'EOD'
Wurden alle Programmpunkte gemeinsam von den deutschen und ausländischen
Jugendlichen der Partnerorganisation durchgeführt? Wenn nein, erläutert bitte,
welche Punkte nicht gemeinsam verbracht worden sind und warum.
EOD,
          NULL,
          ['format' => 'radio']
        ),
        new JsonFormsControl(
          "$scopePrefix/programmpunkteGemeinsamDurchgefuehrtFreitext",
          'Anmerkungen zu (nicht) gemeinsam durchgeführten Punkten',
          NULL,
          ['multi' => TRUE]
        ),

        new JsonFormsControl("$scopePrefix/jugendlicheBeteiligt", <<<'EOD'
  3.3 Bei Jugendbegegnungen: In welcher Form waren die Jugendlichen an der
  Vorbereitung, Durchführung sowie Auswertung des Projekts beteiligt?
EOD, NULL, ['multi' => TRUE]),
        new JsonFormsControl("$scopePrefix/methoden", <<<'EOD'
3.4 Mit welchen Methoden und Programmbausteinen wurde im Projekt gearbeitet?
Welche haben sich bewährt, welche nicht und warum?
EOD, NULL, ['multi' => TRUE]),
        new JsonFormsControl("$scopePrefix/besondere", <<<'EOD'
3.5 Was war das Besondere an der Begegnung? Gab es aus Sicht der
Begegnungsleitung ein Highlight oder herausragende Erlebnisse?
EOD, NULL, ['multi' => TRUE]),
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
          ['multi' => TRUE]
        ),
        new JsonFormsControl("$scopePrefix/teilnahmenachweis", <<<'EOD'
4.3 Stellt Ihr Euren Teilnehmenden einen „Teilnahmenachweis International“ aus
(vgl. <a href="https://www.nachweise-international.de/" target="_blank">www.nachweise-international.de</a>)?
EOD, NULL, ['format' => 'radio']),
        new JsonFormsControl("$scopePrefix/schlussfolgerungen", <<<'EOD'
4.4 Welche Schlussfolgerungen zieht die Leitung aus der Maßnahme? Wie werden die
Erfahrungen durch die Leitung ausgewertet und weitergegeben?
EOD, NULL, ['multi' => TRUE]),
        new JsonFormsControl(
          "$scopePrefix/massnahmenGeplant",
          '4.5 Sind weitere Maßnahmen geplant? Wenn ja, welche?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl("$scopePrefix/veroeffentlichungen", <<<'EOD'
4.6 Welche Veröffentlichungen oder Produkte gab es? Bitte ggf. einen Link zum
Artikel auf der Homepage angeben, Kopie(n) von Pressemitteilung(en),
Belegexemplare etc. bei den Dokumenten hochladen.
EOD, NULL, ['multi' => TRUE]),
        new JsonFormsControl(
          "$scopePrefix/hinweisBMFSFJ",
          '4.7 Wie wurde auf die Förderung durch das BMFSFJ hingewiesen?',
          NULL,
          ['multi' => TRUE]
        ),
        new JsonFormsControl(
          "$scopePrefix/anregungenBMFSFJ",
          '4.8 Welche Anregungen für den Bundearbeitskreis/das BMFSFJ haben sich ggf. aus der Maßnahme ergeben?',
          NULL,
          ['multi' => TRUE]
        ),
      ]),
    ]);
  }

}
