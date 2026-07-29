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

namespace Civi\Funding\FundingCaseTypes\DVV\SonstigeAktivitaet\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class AVK1BeschreibungSchema extends JsonSchemaObject {

  public function __construct() {
    $properties = [
      'thematischeSchwerpunkte' => new JsonSchemaString(['minLength' => 1]),
      'geplanterAblauf' => new JsonSchemaString(['minLength' => 1]),
      'beitragZuPolitischerJugendbildung' => new JsonSchemaString(['minLength' => 1]),
      'zielgruppe' => new JsonSchemaString(['minLength' => 1]),
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
            'qualitaetsentwicklung' => 'Qualitätsentwicklung / Teamendenfortbildung',
            'kinderJugendhilfe' => 'Weiterentwicklung der Kinder- und Jugendhilfe',
          ]),
        ]), ['uniqueItems' => TRUE, 'minItems' => 1]),
      'bildungsanteil' => new JsonSchemaInteger(['minimum' => 0, 'maximum' => 100]),
      'veranstaltungsort' => new JsonSchemaString(['minLength' => 1]),
      'mitSchuleKooperiert' => new JsonSchemaBoolean(),
      'partnerschule' => new JsonSchemaString([
        'minLength' => 1,
        '$limitValidation' => JsonSchema::fromArray([
          'condition' => [
            'evaluate' => [
              'expression' => '!mitSchuleKooperiert',
              'variables' => ['mitSchuleKooperiert' => new JsonSchemaDataPointer('1/mitSchuleKooperiert')],
            ],
          ],
        ]),
      ]),
      'artDerKooperation' => new JsonSchemaString([
        'minLength' => 1,
        '$limitValidation' => JsonSchema::fromArray([
          'condition' => [
            'evaluate' => [
              'expression' => '!mitSchuleKooperiert',
              'variables' => ['mitSchuleKooperiert' => new JsonSchemaDataPointer('1/mitSchuleKooperiert')],
            ],
          ],
        ]),
      ]),
    ];
    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
