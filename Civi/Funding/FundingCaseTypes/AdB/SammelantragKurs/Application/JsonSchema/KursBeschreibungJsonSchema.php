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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class KursBeschreibungJsonSchema extends JsonSchemaObject {

  public const ZIELE_OPTIONS = [
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
  ];

  private const KOOPERATIONSPARTNER_OPTIONS = [
    'keine' => 'keine Kooperationspartner*innen',
    'betrieb' => 'Betrieb',
    'schule' => 'Schule',
    'hochschule' => 'Universität/Fachhichschule',
    'jugendverband' => 'Jugendverband',
    'sportverein' => 'Sportverein',
    'offeneKinderUndJugendarbeit' => 'Offene Kinder- und Jugendarbeit',
    'interkulturelleBildung' => 'Interkulturelle Bildung',
    'kinderUndJugendhilfe' => 'Kinder- und Jugendhilfe',
    'strafvollzug' => 'Jugendstrafvollzug/Justizvollzugsanstalt o.ä.',
    'andere' => 'Andere Kooperationspartner*innen, und zwar:',
  ];

  public function __construct() {
    $properties = [
      'ziele' => new JsonSchemaArray(
        new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf(self::ZIELE_OPTIONS),
        ]), ['uniqueItems' => TRUE, 'minItems' => 1]),
      'bildungsanteil' => new JsonSchemaInteger(['minimum' => 0, 'maximum' => 100]),
      'veranstaltungsort' => new JsonSchemaString(['minLength' => 1]),
      'kooperationspartner' => new JsonSchemaArray(
        new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf(self::KOOPERATIONSPARTNER_OPTIONS),
        ]), ['uniqueItems' => TRUE, 'minItems' => 1]),
      'andereKooperationspartner' => new JsonSchemaString([
        'maxLength' => 300,
        '$validations' => [
          JsonSchema::fromArray([
            'keyword' => 'evaluate',
            'value' => [
              'expression' => 'data != "" || "andere" not in kooperationspartner',
              'variables' => [
                'kooperationspartner' => new JsonSchemaDataPointer('1/kooperationspartner'),
              ],
            ],
            'message' => 'Bitte die anderen Kooperationspartner*innen angeben.',
          ]),
        ],
      ], TRUE),
    ];

    parent::__construct(
      $properties,
      ['required' => ['ziele', 'bildungsanteil', 'veranstaltungsort', 'kooperationspartner']]
    );
  }

}
