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

  public function __construct(\DateTimeInterface $applicationBegin, \DateTimeInterface $applicationEnd) {
    $properties = [
      'kurzbeschreibung' => new JsonSchemaString([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'short_description']]),
      ]),
      'wirktGegenEinsamkeit' => new JsonSchemaString(),
      'kern' => new JsonSchemaString(),
      'status' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'neu' => 'neu startendes Projekt',
          'laeuftSchon' => 'läuft schon seit',
          'sonstiges' => 'sonstiges (was?)',
        ]),
      ]),
      'statusBeginn' => new JsonSchemaDate(),
      'statusSonstiges' => new JsonSchemaString(),
      'foerderungAb' => new JsonSchemaDate([
        'minDate' => $applicationBegin->format('Y-m-d'),
        'maxDate' => $applicationEnd->format('Y-m-d'),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'start_date']]),
      ]),
      'foerderungBis' => new JsonSchemaDate([
        'minDate' => new JsonSchemaDataPointer('1/foerderungAb', '1970-01-01'),
        'maxDate' => $applicationEnd->format('Y-m-d'),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'end_date']]),
      ]),
      'haeufigkeit' => new JsonSchemaString(),
      'beabsichtigteTeilnehmendenzahl' => new JsonSchemaInteger(),
      'zielgruppe' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'kinder' => 'Kinder',
          'jugendliche' => 'Jugendliche',
          'altersaeubergreifend' => 'Altersübergreifend',
          'senioren' => 'Senior:innen',
          'gefluechtete' => 'Geflüchtete',
          'chronischKranke' => 'Chronisch Kranke',
          'erwerbstaetige' => 'Erwerbstätige',
          'arbeitslose' => 'Arbeitslose',
          'sozialBeduerftige' => 'Soziale Bedürftige',
          'jungeMuetter' => 'Junge Mütter',
        ]),
      ]), ['uniqueItems' => TRUE, 'minItems' => 1]),
      'zielgruppeSonstige' => new JsonSchemaString(),
      'zielgruppeErreichen' => new JsonSchemaString(),
      'projektformat' => new JsonSchemaArray(new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'offenesAngebot' => 'Offenes Angebot',
          'regelmaessigeGruppe' => 'Regelmäßige Gruppe',
          'workshop' => 'Workshop',
          'veranstaltung' => 'Veranstaltung',
          'ausfluege' => 'Ausflüge',
          'reisen' => 'Reisen (mit Übernachtung)',
          'material' => 'Material',
          'qualifizierung' => 'Qualifizierung',
        ]),
      ]), ['uniqueItems' => TRUE, 'minItems' => 1]),
      'sonstigesProjektformat' => new JsonSchemaString(),
      'dateien' => new JsonSchemaArray(new JsonSchemaObject([
        '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
        'datei' => new JsonSchemaString([
          'format' => 'uri',
          '$tag' => 'externalFile',
        ]),
        'beschreibung' => new JsonSchemaString(),
      ], ['required' => ['datei', 'beschreibung']])),
      'sonstiges' => new JsonSchemaString(),
    ];

    $keywords = [
      'required' => [
        'kurzbeschreibung',
        'wirktGegenEinsamkeit',
        'kern',
        'status',
        'foerderungAb',
        'foerderungBis',
        'haeufigkeit',
        'beabsichtigteTeilnehmendenzahl',
        'zielgruppe',
        'zielgruppeErreichen',
        'projektformat',
        'dateien',
      ],
    ];

    parent::__construct($properties, $keywords);
  }

}
