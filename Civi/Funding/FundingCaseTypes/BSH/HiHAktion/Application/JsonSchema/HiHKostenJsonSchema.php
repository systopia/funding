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

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class HiHKostenJsonSchema extends JsonSchemaObject {

  public function __construct() {
    $properties = [
      'personalkostenKeine' => new JsonSchemaBoolean(),
      'personalkosten' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readOnly' => TRUE]),
          'posten' => new JsonSchemaString(['maxLength' => 255]),
          'wochenstunden' => new JsonSchemaInteger(['minimum' => 1]),
          'bruttoMonatlich' => new JsonSchemaMoney(['minimum' => 0]),
          'anzahlMonate' => new JsonSchemaInteger(['minimum' => 1]),
          'summe' => new JsonSchemaCalculate('number', 'round(bruttoMonatlich * anzahlMonate, 2)', [
            'bruttoMonatlich' => new JsonSchemaDataPointer('1/bruttoMonatlich'),
            'anzahlMonate' => new JsonSchemaDataPointer('1/anzahlMonate'),
          ]),
        ], ['required' => ['posten', 'wochenstunden', 'bruttoMonatlich', 'anzahlMonate']]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'personalkosten',
            'identifierProperty' => '_identifier',
            'amountProperty' => 'summe',
            'clearing' => [
              'itemLabel' => 'Personalkosten {@pos}',
            ],
          ]),
        ]
      ),
      'personalkostenSumme' => new JsonSchemaCalculate(
        'number', 'round(sum(map(personalkosten, "value.summe")), 2)', [
          'personalkosten' => new JsonSchemaDataPointer('1/personalkosten'),
        ]
      ),
      'personalkostenKommentar' => new JsonSchemaString(['maxLength' => 4000]),
      'honorareKeine' => new JsonSchemaBoolean(),
      'honorare' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'posten' => new JsonSchemaString(['maxLength' => 255]),
          'berechnungsgrundlage' => new JsonSchemaString([
            'oneOf' => JsonSchemaUtil::buildTitledOneOf([
              'stundensatz' => 'Stundensatz',
              'tagessatz' => 'Tagessatz',
            ]),
          ]),
          'verguetung' => new JsonSchemaMoney(['minimum' => 0]),
          'dauer' => new JsonSchemaNumber(['precision' => 2, 'minimum' => 0]),
          'summe' => new JsonSchemaCalculate(
            'number',
            'round(dauer * verguetung, 2)',
            [
              'dauer' => new JsonSchemaDataPointer('1/dauer'),
              'verguetung' => new JsonSchemaDataPointer('1/verguetung'),
            ]
          ),
        ], ['required' => ['posten', 'berechnungsgrundlage', 'verguetung', 'dauer']]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'honorar',
            'identifierProperty' => '_identifier',
            'amountProperty' => 'summe',
            'clearing' => [
              'itemLabel' => 'Honorar {@pos}',
            ],
          ]),
        ]
      ),
      'honorareSumme' => new JsonSchemaCalculate(
        'number', 'round(sum(map(honorare, "value.summe")), 2)', [
          'honorare' => new JsonSchemaDataPointer('1/honorare'),
        ]
      ),
      'honorareKommentar' => new JsonSchemaString(['maxLength' => 4000]),
      'sachkostenKeine' => new JsonSchemaBoolean(),
      'sachkosten' => new JsonSchemaObject([
        'materialien' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.materialien',
            'identifier' => 'sachkosten.materialien',
            'clearing' => [
              'itemLabel' => 'Projektbezogene Materialien',
            ],
          ]),
        ]),
        'ehrenamtspauschalen' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.ehrenamtspauschalen',
            'identifier' => 'sachkosten.ehrenamtspauschalen',
            'clearing' => [
              'itemLabel' => 'Ehrenamts-/Übungsleiterpauschalen',
            ],
          ]),
        ]),
        'verpflegung' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.verpflegung',
            'identifier' => 'sachkosten.verpflegung',
            'clearing' => [
              'itemLabel' => 'Verpflegung/Catering',
            ],
          ]),
        ]),
        'fahrtkosten' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.fahrtkosten',
            'identifier' => 'sachkosten.fahrtkosten',
            'clearing' => [
              'itemLabel' => 'Fahrtkosten',
            ],
          ]),
        ]),
        'oeffentlichkeitsarbeit' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.oeffentlichkeitsarbeit',
            'identifier' => 'sachkosten.oeffentlichkeitsarbeit',
            'clearing' => [
              'itemLabel' => 'Projektbezogene Öffentlichkeitsarbeit',
            ],
          ]),
        ]),
        'investitionen' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.investitionen',
            'identifier' => 'sachkosten.investitionen',
            'clearing' => [
              'itemLabel' => 'Projektbezogene Investitionen',
            ],
          ]),
        ]),
        'mieten' => new JsonSchemaMoney([
          'default' => 0,
          'minimum' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'sachkosten.mieten',
            'identifier' => 'sachkosten.mieten',
            'clearing' => [
              'itemLabel' => 'Projektbezogene Mieten',
            ],
          ]),
        ]),
        'sonstige' => new JsonSchemaArray(
          new JsonSchemaObject([
            '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
            'bezeichnung' => new JsonSchemaString(['maxLength' => 255]),
            'summe' => new JsonSchemaMoney(['minimum' => 0]),
          ], ['required' => ['bezeichnung', 'summe']]),
          [
            '$costItems' => new JsonSchemaCostItems([
              'type' => 'sachkosten.sonstige',
              'identifierProperty' => '_identifier',
              'amountProperty' => 'summe',
              'clearing' => [
                'itemLabel' => 'Sonstige Sachkosten {@pos}',
              ],
            ]),
          ]
        ),
        'sonstigeSumme' => new JsonSchemaCalculate(
          'number',
          'round(sum(map(sonstige, "value.summe")), 2)',
          ['sonstige' => new JsonSchemaDataPointer('1/sonstige')]
        ),
        'summe' => new JsonSchemaCalculate(
          'number',
          'round(materialien + ehrenamtspauschalen + verpflegung + fahrtkosten + oeffentlichkeitsarbeit'
          . '+ investitionen + mieten + sonstigeSumme, 2)',
          [
            'materialien' => new JsonSchemaDataPointer('1/materialien', 0),
            'ehrenamtspauschalen' => new JsonSchemaDataPointer('1/ehrenamtspauschalen', 0),
            'verpflegung' => new JsonSchemaDataPointer('1/verpflegung', 0),
            'fahrtkosten' => new JsonSchemaDataPointer('1/fahrtkosten', 0),
            'oeffentlichkeitsarbeit' => new JsonSchemaDataPointer('1/oeffentlichkeitsarbeit', 0),
            'investitionen' => new JsonSchemaDataPointer('1/investitionen', 0),
            'mieten' => new JsonSchemaDataPointer('1/mieten', 0),
            'sonstigeSumme' => new JsonSchemaDataPointer('1/sonstigeSumme'),
          ]
        ),
      ], [
        'required' => [
          'materialien',
          'ehrenamtspauschalen',
          'verpflegung',
          'fahrtkosten',
          'oeffentlichkeitsarbeit',
          'investitionen',
          'mieten',
          'sonstige',
        ],
      ]),
      'sachkostenKommentar' => new JsonSchemaString(['maxLength' => 4000]),
      'gesamtkosten' => new JsonSchemaCalculate(
        'number',
        'round(personalkostenSumme + honorareSumme + sachkostenSumme, 2)',
        [
          'personalkostenSumme' => new JsonSchemaDataPointer('1/personalkostenSumme'),
          'honorareSumme' => new JsonSchemaDataPointer('1/honorareSumme'),
          'sachkostenSumme' => new JsonSchemaDataPointer('1/sachkosten/summe'),
        ],
        NULL,
        ['$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']])],
      ),
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

    $keywords = [
      'required' => [
        'personalkosten',
        'honorare',
        'sachkostenKeine',
        'sachkosten',
      ],
      'allOf' => [
        JsonSchema::fromArray([
          'if' => [
            'properties' => [
              'sachkostenSumme' => ['exclusiveMinimum' => 0],
            ],
          ],
          'then' => new JsonSchemaObject([
            'sachkostenKommentar' => new JsonSchemaString($minLengthValidation),
          ], ['required' => ['sachkostenKommentar']]),
        ]),
        JsonSchema::fromArray([
          'if' => [
            'properties' => [
              'personalkostenSumme' => ['exclusiveMinimum' => 0],
            ],
          ],
          'then' => new JsonSchemaObject([
            'personalkostenKommentar' => new JsonSchemaString($minLengthValidation),
          ], ['required' => ['personalkostenKommentar']]),
        ]),
        JsonSchema::fromArray([
          'if' => [
            'properties' => [
              'honorareSumme' => ['exclusiveMinimum' => 0],
            ],
          ],
          'then' => new JsonSchemaObject([
            'honorareKommentar' => new JsonSchemaString($minLengthValidation),
          ], ['required' => ['honorareKommentar']]),
        ]),
      ],
    ];

    parent::__construct($properties, $keywords);
  }

}
