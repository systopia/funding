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

namespace Civi\Funding\SonstigeAktivitaet\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItem;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItems;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class AVK1FinanzierungSchema extends JsonSchemaObject {

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
      // Abschnitt II.4
      'sonstigeMittel' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'quelle' => new JsonSchemaString(),
          'betrag' => new JsonSchemaMoney(['minimum' => 0]),
        ], ['required' => ['betrag', 'quelle']]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'sonstigeMittel',
            'identifierProperty' => '_identifier',
            'amountProperty' => 'betrag',
          ]),
        ]
      ),
      'sonstigeMittelGesamt' => new JsonSchemaCalculate(
        'number',
        'round(sum(map(sonstigeMittel, "value.betrag")), 2)',
        ['sonstigeMittel' => new JsonSchemaDataPointer('1/sonstigeMittel')]
      ),
      // Gesamtmittel ohne Zuschuss
      'gesamtmittel' => new JsonSchemaCalculate(
        'number',
        'round(teilnehmerbeitraege + eigenmittel + oeffentlicheMittelGesamt + sonstigeMittelGesamt, 2)',
        [
          'teilnehmerbeitraege' => new JsonSchemaDataPointer('1/teilnehmerbeitraege'),
          'eigenmittel' => new JsonSchemaDataPointer('1/eigenmittel', 0),
          'oeffentlicheMittelGesamt' => new JsonSchemaDataPointer('1/oeffentlicheMittelGesamt'),
          'sonstigeMittelGesamt' => new JsonSchemaDataPointer('1/sonstigeMittelGesamt'),
        ],
        NULL,
        ['minimum' => 0.01],
      ),
      // Beantragter Zuschuss
      'beantragterZuschuss' => new JsonSchemaCalculate('number', 'round(max(gesamtkosten - gesamtmittel, 0), 2)', [
        'gesamtkosten' => new JsonSchemaDataPointer('/kosten/gesamtkosten'),
        'gesamtmittel' => new JsonSchemaDataPointer('1/gesamtmittel'),
      ]),
    ], [
      'required' => [
        'oeffentlicheMittel',
        'sonstigeMittel',
      ],
    ]);
  }

}
