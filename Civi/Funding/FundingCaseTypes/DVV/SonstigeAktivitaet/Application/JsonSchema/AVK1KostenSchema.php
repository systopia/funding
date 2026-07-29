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

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class AVK1KostenSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      // Abschnitt I.1
      'unterkunftUndVerpflegung' => new JsonSchemaMoney([
        'minimum' => 0,
        'default' => 0,
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'unterkunftUndVerpflegung',
          'identifier' => 'unterkunftUndVerpflegung',
          'clearing' => [
            'itemLabel' => 'Unterkunft und Verpflegung',
          ],
        ]),
      ]),
      // Abschnitt I.2
      'honorare' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'berechnungsgrundlage' => new JsonSchemaString([
            'oneOf' => JsonSchemaUtil::buildTitledOneOf([
              'stundensatz' => 'Stundensatz',
              'tagessatz' => 'Tagessatz',
            ]),
          ]),
          'dauer' => new JsonSchemaNumber(['precision' => 2, 'minimum' => 0]),
          'verguetung' => new JsonSchemaMoney(['minimum' => 0]),
          'leistung' => new JsonSchemaString(),
          'qualifikation' => new JsonSchemaString(),
          'betrag' => new JsonSchemaCalculate(
            'number',
            'round(dauer * verguetung, 2)',
            [
              'dauer' => new JsonSchemaDataPointer('1/dauer'),
              'verguetung' => new JsonSchemaDataPointer('1/verguetung'),
            ]
          ),
        ], ['required' => ['berechnungsgrundlage', 'dauer', 'verguetung', 'leistung', 'qualifikation']]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'honorar',
            'identifierProperty' => '_identifier',
            'amountProperty' => 'betrag',
            'clearing' => [
              'itemLabel' => 'Honorar {@pos}',
            ],
          ]),
        ]
      ),
      'honorareGesamt' => new JsonSchemaCalculate('number', 'round(sum(map(honorare, "value.betrag")), 2)', [
        'honorare' => new JsonSchemaDataPointer('1/honorare'),
      ]),
      // Abschnitt I.4
      'fahrtkosten' => new JsonSchemaObject([
        'teilnehmer' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'fahrtkosten/teilnehmer',
            'identifier' => 'fahrtkosten.teilnehmer',
            'clearing' => [
              'itemLabel' => 'Fahrtkosten Teilnehmer*innen',
            ],
          ]),
        ]),
        'referenten' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'fahrtkosten/referenten',
            'identifier' => 'fahrtkosten.referenten',
            'clearing' => [
              'itemLabel' => 'Fahrtkosten Referent*innen',
            ],
          ]),
        ]),
      ]),
      'fahrtkostenGesamt' => new JsonSchemaCalculate('number', 'round(teilnehmer + referenten, 2)', [
        'teilnehmer' => new JsonSchemaDataPointer('1/fahrtkosten/teilnehmer'),
        'referenten' => new JsonSchemaDataPointer('1/fahrtkosten/referenten'),
      ]),
      // Abschnitt I.5
      'sachkosten' => new JsonSchemaObject([
        'ausstattung' => new JsonSchemaArray(
          new JsonSchemaObject([
            '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
            'gegenstand' => new JsonSchemaString(),
            'betrag' => new JsonSchemaMoney(['minimum' => 0]),
          ], ['required' => ['gegenstand', 'betrag']]),
          [
            '$costItems' => new JsonSchemaCostItems([
              'type' => 'sachkosten/ausstattung',
              'identifierProperty' => '_identifier',
              'amountProperty' => 'betrag',
              'clearing' => [
                'itemLabel' => 'Sachkosten {@pos}',
              ],
            ]),
          ]
        ),
      ], ['required' => ['ausstattung']]),
      'sachkostenGesamt' => new JsonSchemaCalculate(
        'number',
        'round(sum(map(ausstattung, "value.betrag")), 2)',
        [
          'ausstattung' => new JsonSchemaDataPointer('1/sachkosten/ausstattung'),
        ]
      ),
      // Abschnitt I.6
      'sonstigeAusgaben' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'betrag' => new JsonSchemaMoney(['minimum' => 0]),
          'zweck' => new JsonSchemaString(),
        ], ['required' => ['betrag', 'zweck']]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'sonstigeAusgabe',
            'identifierProperty' => '_identifier',
            'amountProperty' => 'betrag',
            'clearing' => [
              'itemLabel' => 'Sonstige Ausgabe {@pos}',
            ],
          ]),
        ]
      ),
      'sonstigeAusgabenGesamt' => new JsonSchemaCalculate(
        'number',
        'round(sum(map(sonstigeAusgaben, "value.betrag")), 2)',
        ['sonstigeAusgaben' => new JsonSchemaDataPointer('1/sonstigeAusgaben')]
      ),
      // Gesamtkosten
      'gesamtkosten' => new JsonSchemaCalculate(
        'number',
        'round(unterkunftUndVerpflegung + honorareGesamt + fahrtkostenGesamt + sachkostenGesamt
        + sonstigeAusgabenGesamt, 2)',
        [
          'unterkunftUndVerpflegung' => new JsonSchemaDataPointer('1/unterkunftUndVerpflegung'),
          'honorareGesamt' => new JsonSchemaDataPointer('1/honorareGesamt'),
          'fahrtkostenGesamt' => new JsonSchemaDataPointer('1/fahrtkostenGesamt'),
          'sachkostenGesamt' => new JsonSchemaDataPointer('1/sachkostenGesamt'),
          'sonstigeAusgabenGesamt' => new JsonSchemaDataPointer('1/sonstigeAusgabenGesamt'),
        ]
      ),
    ], [
      'required' => [
        'honorare',
        'fahrtkosten',
        'sachkosten',
        'sonstigeAusgaben',
      ],
    ]);
  }

}
