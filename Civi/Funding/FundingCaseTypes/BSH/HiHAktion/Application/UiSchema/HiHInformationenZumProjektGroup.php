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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema;

use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class HiHInformationenZumProjektGroup extends JsonFormsGroup {

  public function __construct(string $scopePrefix) {
    parent::__construct('Informationen zum Projekt', [
      new JsonFormsControl(
        "$scopePrefix/kurzbeschreibung",
        <<<EOD
Bitte beschreiben Sie ihr Projekt. Gehen sie dabei besonders darauf ein, was sie
konkret mit der Förderung umsetzen wollen):
EOD, NULL, ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/wirktGegenEinsamkeit",
        'Wie wirkt das Projekt gegen Einsamkeit?',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/kern",
        'Kurztext für den NDR: Was ist der Kern Ihres Projektes? (1-2 Sätze)',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/status",
        'Welchen Status hat das Projekt?',
      ),
      new JsonFormsControl(
        "$scopePrefix/statusBeginn",
        'Läuft seit',
        NULL,
        ['hideLabel' => TRUE],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/status",
            JsonSchema::fromArray(['const' => 'laeuftSchon'])
          ),
        ]
      ),
      new JsonFormsControl(
        "$scopePrefix/statusSonstiges",
        'Sonstiger Status',
        NULL,
        ['hideLabel' => TRUE],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/status",
            JsonSchema::fromArray(['const' => 'sonstiges'])
          ),
        ]
      ),
      new JsonFormsGroup('Für welchen Zeitraum beantragen Sie die Förderung?', [
        new JsonFormsControl(
          "$scopePrefix/foerderungAb",
          'Ab',
        ),
        new JsonFormsControl(
          "$scopePrefix/foerderungBis",
          'Bis',
        ),
      ]),
      new JsonFormsControl(
        "$scopePrefix/beabsichtigteTeilnehmendenzahl",
        'Wie viele Teilnehmende wollen Sie erreichen?',
      ),
      new JsonFormsControl(
        "$scopePrefix/zielgruppe",
        'Wer ist Ihre Zielgruppe? (Mehrfachnennungen möglich)',
      ),
      new JsonFormsControl(
        "$scopePrefix/zielgruppeErreichen",
        'Wie erreichen Sie die Zielgruppe?',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/zielgruppeHerausforderungen",
        'Mit welchen Herausforderungen hat Ihre Zielgruppe zu kämpfen?'
      ),
      new JsonFormsControl(
        "$scopePrefix/zielgruppeHerausforderungenSonstige",
        'Sonstige Herausforderungen',
        NULL,
        ['hideLabel' => TRUE],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/zielgruppeHerausforderungen",
            JsonSchema::fromArray(['contains' => ['const' => 'sonstige']])
          ),
        ]
      ),
      new JsonFormsControl(
        "$scopePrefix/zielgruppeHerausforderungenErlaeuterung",
        'Bitte erläutern Sie die Herausforderungen ihrer Zielgruppe detaillierter:',
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsControl(
        "$scopePrefix/projektformat",
        'Welches Projektformat planen Sie umzusetzen? (Mehrfachnennung möglich)',
      ),
      new JsonFormsControl(
        "$scopePrefix/projektformatSonstiges",
        'Sonstiges Projektformat',
        NULL,
        ['hideLabel' => TRUE],
        [
          'rule' => new JsonFormsRule(
            'SHOW',
            "$scopePrefix/projektformat",
            JsonSchema::fromArray(['contains' => ['const' => 'sonstiges']])
          ),
        ]
      ),
      new JsonFormsControl(
        "$scopePrefix/projektformatErlaeuterung",
        <<<EOD
Bitte erläutern Sie das Projektformat: Wie funktioniert das Angebot, wie oft
findet es statt und worum geht es?
EOD,
        NULL,
        ['multi' => TRUE]
      ),
      new JsonFormsArray(
        "$scopePrefix/dateien",
        'Gibt es bereits eine Beschreibung zu Ihrem Projekt? Flyer oder andere Materialien hochladen.',
        NULL,
        [
          new JsonFormsHidden('#/properties/_identifier'),
          new JsonFormsControl('#/properties/datei', 'Datei', NULL, ['format' => 'file']),
          new JsonFormsControl('#/properties/beschreibung', 'Beschreibung'),
        ],
        [
          'addButtonLabel' => 'Datei hinzufügen',
          'removeButtonLabel' => 'Datei entfernen',
        ]
      ),
      new JsonFormsControl(
        "$scopePrefix/sonstiges",
        'Was Sie uns sonst noch zu Ihrem Projekt sagen wollen',
        NULL,
        ['multi' => TRUE]
      ),
    ]);
  }

}
