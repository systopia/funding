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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema;

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
        'intern' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'fahrtkosten/intern',
            'identifier' => 'fahrtkosten.intern',
            'clearing' => [
              'itemLabel' => 'Fahrtkosten innerhalb des Programms',
            ],
          ]),
        ]),
        'anTeilnehmerErstattet' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'fahrtkosten/anTeilnehmerErstattet',
            'identifier' => 'fahrtkosten.anTeilnehmerErstattet',
            'clearing' => [
              'itemLabel' => 'An Teilnehmer*innen/Referent*innen erstattete Fahrtkosten',
            ],
          ]),
        ]),
      ]),
      'fahrtkostenGesamt' => new JsonSchemaCalculate('number', 'round(intern + anTeilnehmerErstattet, 2)', [
        'intern' => new JsonSchemaDataPointer('1/fahrtkosten/intern'),
        'anTeilnehmerErstattet' => new JsonSchemaDataPointer('1/fahrtkosten/anTeilnehmerErstattet'),
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
      // Abschnitt I.7
      'versicherung' => new JsonSchemaObject([
        'teilnehmer' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'versicherung/teilnehmer',
            'identifier' => 'versicherung.teilnehmer',
            'clearing' => [
              'itemLabel' => 'Kosten der Versicherung der Teilnehmer*innen',
            ],
          ]),
        ]),
      ]),
      // Gesamtkosten
      'gesamtkosten' => new JsonSchemaCalculate(
        'number',
        'round(unterkunftUndVerpflegung + honorareGesamt + fahrtkostenGesamt + sachkostenGesamt
        + sonstigeAusgabenGesamt + versicherungTeilnehmer, 2)',
        [
          'unterkunftUndVerpflegung' => new JsonSchemaDataPointer('1/unterkunftUndVerpflegung'),
          'honorareGesamt' => new JsonSchemaDataPointer('1/honorareGesamt'),
          'fahrtkostenGesamt' => new JsonSchemaDataPointer('1/fahrtkostenGesamt'),
          'sachkostenGesamt' => new JsonSchemaDataPointer('1/sachkostenGesamt'),
          'sonstigeAusgabenGesamt' => new JsonSchemaDataPointer('1/sonstigeAusgabenGesamt'),
          'versicherungTeilnehmer' => new JsonSchemaDataPointer('1/versicherung/teilnehmer'),
        ]
      ),
    ], [
      'required' => [
        'honorare',
        'fahrtkosten',
        'sachkosten',
        'sonstigeAusgaben',
        'versicherung',
      ],
    ]);
  }

}
