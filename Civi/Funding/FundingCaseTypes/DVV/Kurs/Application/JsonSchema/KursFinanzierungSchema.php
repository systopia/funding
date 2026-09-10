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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItem;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursFinanzierungSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      // Abschnitt II.1
      'teilnehmerbeitraege' => new JsonSchemaMoney([
        'minimum' => 0,
        'default' => 0,
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'teilnehmerbeitraege',
          'identifier' => 'teilnehmerbeitraege',
        ]),
      ]),
      // Abschnitt II.2
      'eigenmittel' => new JsonSchemaMoney([
        'minimum' => 0,
        'default' => 0,
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'eigenmittel',
          'identifier' => 'eigenmittel',
        ]),
      ]),
      'spenden' => new JsonSchemaMoney([
        'minimum' => 0,
        'default' => 0,
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'spenden',
          'identifier' => 'spenden',
        ]),
      ]),
      // Abschnitt II.3
      'oeffentlicheMittel' => new JsonSchemaObject([
        'europa' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'oeffentlicheMittel/europa',
            'identifier' => 'oeffentlicheMittel.europa',
          ]),
        ]),
        'bundeslaender' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'oeffentlicheMittel/bundeslaender',
            'identifier' => 'oeffentlicheMittel.bundeslaender',
          ]),
        ]),
        'staedteUndKreise' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'oeffentlicheMittel/staedteUndKreise',
            'identifier' => 'oeffentlicheMittel.staedteUndKreise',
          ]),
        ]),
      ]),
      'oeffentlicheMittelGesamt' => new JsonSchemaCalculate(
        'number',
        'round(europa + bundeslaender + staedteUndKreise, 2)',
        [
          'europa' => new JsonSchemaDataPointer('1/oeffentlicheMittel/europa'),
          'bundeslaender' => new JsonSchemaDataPointer('1/oeffentlicheMittel/bundeslaender'),
          'staedteUndKreise' => new JsonSchemaDataPointer('1/oeffentlicheMittel/staedteUndKreise'),
        ]
      ),
      // Gesamtmittel ohne Zuschuss
      'gesamtmittel' => new JsonSchemaCalculate(
        'number',
        'round(teilnehmerbeitraege + eigenmittel + spenden + oeffentlicheMittelGesamt, 2)',
        [
          'teilnehmerbeitraege' => new JsonSchemaDataPointer('1/teilnehmerbeitraege', 0),
          'eigenmittel' => new JsonSchemaDataPointer('1/eigenmittel', 0),
          'spenden' => new JsonSchemaDataPointer('1/spenden', 0),
          'oeffentlicheMittelGesamt' => new JsonSchemaDataPointer('1/oeffentlicheMittelGesamt'),
        ],
      ),
      // Beantragter Zuschuss
      'beantragterZuschuss' => new JsonSchemaCalculate('number', 'round(max(gesamtkosten - gesamtmittel, 0), 2)', [
        'gesamtkosten' => new JsonSchemaDataPointer('/kosten/gesamtkosten'),
        'gesamtmittel' => new JsonSchemaDataPointer('1/gesamtmittel'),
      ], NULL, ['$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']])]),
      'gesamtfinanzierung' => new JsonSchemaCalculate('number', 'round(beantragterZuschuss + gesamtmittel, 2)', [
        'beantragterZuschuss' => new JsonSchemaDataPointer('1/beantragterZuschuss'),
        'gesamtmittel' => new JsonSchemaDataPointer('1/gesamtmittel'),
      ]),
    ], [
      'required' => [
        'oeffentlicheMittel',
        'spenden',
      ],
    ]);
  }

}
