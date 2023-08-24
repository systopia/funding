<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Form\SonstigeAktivitaet\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class AVK1BeschreibungSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'thematischeSchwerpunkte' => new JsonSchemaString(),
      'geplanterAblauf' => new JsonSchemaString(),
      'beitragZuPolitischerJugendbildung' => new JsonSchemaString(),
      'zielgruppe' => new JsonSchemaString(),
      'ziele' => new JsonSchemaArray(
        new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf([
            'persoenlichkeitsbildung' => 'Persönlichkeitsbildung',
            'toleranzRespektDemokratie' => 'Förderung von Toleranz, Respekt und Demokratie',
            'chancengleichheit' => 'Chancengleichheit, Abbau von Diskriminierungen und sozialer Ungerechtigkeit',
            'jungeMenschen' => 'Stärkung der Beteiligung junger Menschen',
            'migrantisierteMenschen'
            => 'Stärkung der Teilhabe, Verbesserung der Bedingungen für migrantisierte Menschen',
            'gefaehrdungMissbrauchGewalt'
            => 'Schutz vor Gefährdungen, Missbrauch und Gewalt und Befähigung zum kritischen Umgang mit Risiken',
            'jugendpolitischeAnliegen' => 'Stärkung jugendpolitischer Anliegen auf nationaler und europäischer Ebene',
            'internationaleBegegnungen' => 'Stärkung europäischer und internationaler Begegnungen und Erfahrungen',
            'qualitaetsentwicklung' => 'Qualitätsentwicklung / Teamendenfortbildung',
            'kinderJugendhilfe' => 'Weiterentwicklung der Kinder- und Jugendhilfe',
          ]),
        ]), ['uniqueItems' => TRUE]),
      'bildungsanteil' => new JsonSchema(['type' => ['integer', 'null'], 'minimum' => 0, 'maximum' => 100]),
      'veranstaltungsort' => new JsonSchemaString(),
      'partner' => new JsonSchemaString(),
    ]);
  }

}
