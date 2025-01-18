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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class HiHInformationenZumProjektJsonSchema extends JsonSchemaObject {

  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    JsonSchema $notSaveIf
  ) {
    $properties = [
      'kurzbeschreibung' => new JsonSchemaString([
        'maxLength' => 1800,
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'short_description']]),
      ]),
      'wirktGegenEinsamkeit' => new JsonSchemaString(['maxLength' => 900]),
      'ziel' => new JsonSchemaString(['maxLength' => 300]),
      'status' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf2([
          '- Bitte auswählen -' => '',
          'neu startendes Projekt' => 'neu',
          'läuft schon seit' => 'laeuftSchon',
        ]),
      ]),
      'statusBeginn' => new JsonSchemaDate(['maxDate' => date('Y-m-d')], TRUE),
      'foerderungAb' => new JsonSchemaDate([
        'minDate' => $applicationBegin->format('Y-m-d'),
        'maxDate' => $applicationEnd->format('Y-m-d'),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'start_date']]),
      ], TRUE),
      'foerderungBis' => new JsonSchemaDate([
        'minDate' => new JsonSchemaDataPointer('1/foerderungAb', '1970-01-01'),
        'maxDate' => $applicationEnd->format('Y-m-d'),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'end_date']]),
      ], TRUE),
      'beabsichtigteTeilnehmendenzahl' => new JsonSchemaInteger(['minimum' => 1], TRUE),
      'zielgruppe' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'kinder' => 'Kinder (0-12 Jahre)',
          'jugendliche' => 'Jugendliche (13-19 Jahre)',
          'jungeErwachsene' => 'Junge Erwachsene (20-29 Jahre)',
          'erwachsene' => 'Erwachsene (30-49 Jahre)',
          'aeltereErwachsene' => 'Ältere Erwachsene (50-66 Jahre)',
          'senioren' => 'Senior:innen (ab 67 Jahre)',
          'altersaeubergreifend' => 'Altersübergreifend',
        ]),
      ]), ['uniqueItems' => TRUE]),
      'zielgruppeErreichen' => new JsonSchemaString(['maxLength' => 900]),
      'zielgruppeHerausforderungen' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'fluchterfahrung' => 'Mit Fluchterfahrung',
          'diskriminierung' => 'Mit Diskriminierung und/oder Rassismus',
          'armut' => 'Mit Armut',
          'erwerbslosigkeit' => 'Mit Erwerbslosigkeit',
          'pflegeVonAngehoerigen' => 'Mit dem Pflegen von Angehörigen',
          'alleinerziehend' => 'Weil sie alleinerziehend sind',
          'sonstige' => 'Aus sonstigen Gründen',
        ]),
      ]), ['uniqueItems' => TRUE]),
      'zielgruppeHerausforderungenSonstige' => new JsonSchemaString(['maxLength' => 255]),
      'zielgruppeHerausforderungenErlaeuterung' => new JsonSchemaString(['maxLength' => 900]),
      'projektformat' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'offenesAngebot' => 'Offenes Angebot',
          'regelmaessigeGruppe' => 'Regelmäßige Gruppe',
          'workshop' => 'Workshop',
          'veranstaltung' => 'Veranstaltung',
          'ausfluege' => 'Ausflüge',
          'reisen' => 'Reisen (mit Übernachtung)',
          'qualifizierung' => 'Qualifizierung',
          'sonstiges' => 'Sonstiges und zwar',
        ]),
      ]), ['uniqueItems' => TRUE]),
      'projektformatSonstiges' => new JsonSchemaString(['maxLength' => 255]),
      'projektformatErlaeuterung' => new JsonSchemaString(['maxLength' => 900]),
      'dateien' => new JsonSchemaArray(new JsonSchemaObject([
        '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
        'datei' => new JsonSchemaString([
          'maxLength' => 255,
          'format' => 'uri',
        ]),
        'beschreibung' => new JsonSchemaString(['maxLength' => 255]),
      ], ['required' => ['datei']])),
      'sonstiges' => new JsonSchemaString(['maxLength' => 900]),
    ];

    $minLengthValidation = [
      '$validations' => [
        JsonSchema::fromArray([
          'keyword' => 'minLength',
          'value' => 1,
          'message' => 'Dieser Wert ist erforderlich.',
        ]),
      ],
    ];
    $stringValidation = [
      '$validations' => [
        JsonSchema::fromArray([
          'keyword' => 'type',
          'value' => 'string',
          'message' => 'Dieser Wert ist erforderlich.',
        ]),
      ],
    ];
    $integerValidation = [
      '$validations' => [
        JsonSchema::fromArray([
          'keyword' => 'type',
          'value' => 'integer',
          'message' => 'Dieser Wert ist erforderlich.',
        ]),
      ],
    ];

    $keywords = [
      'required' => ['kurzbeschreibung', 'dateien'],
      'if' => $notSaveIf,
      'then' => JsonSchema::fromArray([
        'properties' => [
          'wirktGegenEinsamkeit' => new JsonSchemaString($minLengthValidation),
          'ziel' => new JsonSchemaString($minLengthValidation),
          'status' => new JsonSchemaString($minLengthValidation),
          'foerderungAb' => new JsonSchema($stringValidation),
          'foerderungBis' => new JsonSchema($stringValidation),
          'beabsichtigteTeilnehmendenzahl' => new JsonSchema($integerValidation),
          'zielgruppe' => new JsonSchemaArray(new JsonSchemaString(), ['minItems' => 1]),
          'zielgruppeHerausforderungen' => new JsonSchemaArray(new JsonSchemaString(), ['minItems' => 1]),
          'projektformat' => new JsonSchemaArray(new JsonSchemaString(), ['minItems' => 1]),
          'projektformatErlaeuterung' => new JsonSchemaString($minLengthValidation),
        ],
        'required' => [
          'wirktGegenEinsamkeit',
          'ziel',
          'status',
          'foerderungAb',
          'foerderungBis',
          'beabsichtigteTeilnehmendenzahl',
          'zielgruppe',
          'zielgruppeErreichen',
          'zielgruppeHerausforderungen',
          'zielgruppeHerausforderungenErlaeuterung',
          'projektformat',
          'projektformatErlaeuterung',
        ],
        'allOf' => [
          JsonSchema::fromArray([
            'if' => [
              'properties' => [
                'status' => ['const' => 'laeuftSchon'],
              ],
            ],
            'then' => new JsonSchemaObject([
              'statusBeginn' => new JsonSchemaDate([
                'maxDate' => date('Y-m-d'),
                '$validations' => [
                  JsonSchema::fromArray([
                    'keyword' => 'type',
                    'value' => 'string',
                    'message' => 'Dieser Wert ist erforderlich.',
                  ]),
                ],
              ], TRUE),
            ], ['required' => ['statusBeginn']]),
          ]),
          JsonSchema::fromArray([
            'if' => [
              'properties' => [
                'zielgruppeHerausforderungen' => ['contains' => ['const' => 'sonstige']],
              ],
            ],
            'then' => new JsonSchemaObject([
              'zielgruppeHerausforderungenSonstige' => new JsonSchemaString($minLengthValidation),
            ], ['required' => ['zielgruppeHerausforderungenSonstige']]),
          ]),
          JsonSchema::fromArray([
            'if' => [
              'properties' => [
                'projektformat' => ['contains' => ['const' => 'sonstiges']],
              ],
            ],
            'then' => new JsonSchemaObject([
              'projektformatSonstiges' => new JsonSchemaString($minLengthValidation),
            ], ['required' => ['projektformatSonstiges']]),
          ]),
        ],
      ]),
    ];

    parent::__construct($properties, $keywords);
  }

}
