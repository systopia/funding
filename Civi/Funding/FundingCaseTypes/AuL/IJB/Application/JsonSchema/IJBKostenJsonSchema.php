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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema;

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

final class IJBKostenJsonSchema extends JsonSchemaObject {

  public function __construct() {
    $properties = [
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
      'fahrtkosten' => new JsonSchemaObject([
        'flug' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'fahrtkosten/flug',
            'identifier' => 'fahrtkosten.flug',
            'clearing' => [
              'itemLabel' => 'Fahrt-/Flugkosten inkl. Transfer zur Unterkunft',
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
              'itemLabel' => 'An Teilnehmer*innen erstattete Fahrtkosten',
            ],
          ]),
        ]),
      ]),
      'fahrtkostenGesamt' => new JsonSchemaCalculate('number', 'round(flug + anTeilnehmerErstattet, 2)', [
        'flug' => new JsonSchemaDataPointer('1/fahrtkosten/flug'),
        'anTeilnehmerErstattet' => new JsonSchemaDataPointer('1/fahrtkosten/anTeilnehmerErstattet'),
      ]),
      'programmkosten' => new JsonSchemaObject([
        'programmkosten' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'programmkosten/programmkosten',
            'identifier' => 'programmkosten.programmkosten',
            'clearing' => [
              'itemLabel' => 'Programmkosten',
            ],
          ]),
        ]),
        'arbeitsmaterial' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'programmkosten/arbeitsmaterial',
            'identifier' => 'programmkosten.arbeitsmaterial',
            'clearing' => [
              'itemLabel' => 'Arbeitsmaterial',
            ],
          ]),
        ]),
        'fahrt' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'programmkosten/fahrt',
            'identifier' => 'programmkosten.fahrt',
            'clearing' => [
              'itemLabel' => 'Programmfahrtkosten',
            ],
          ]),
        ]),
      ]),
      'programmkostenGesamt' => new JsonSchemaCalculate(
        'number',
        'round(programmkosten + arbeitsmaterial + fahrt, 2)',
        [
          'programmkosten' => new JsonSchemaDataPointer('1/programmkosten/programmkosten'),
          'arbeitsmaterial' => new JsonSchemaDataPointer('1/programmkosten/arbeitsmaterial'),
          'fahrt' => new JsonSchemaDataPointer('1/programmkosten/fahrt'),
        ]
      ),
      'sonstigeKosten' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'gegenstand' => new JsonSchemaString(),
          'betrag' => new JsonSchemaMoney(['minimum' => 0]),
        ], ['required' => ['gegenstand', 'betrag']]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'sonstigeKosten',
            'identifierProperty' => '_identifier',
            'amountProperty' => 'betrag',
            'clearing' => [
              'itemLabel' => 'Sonstige Kosten {@pos}',
            ],
          ]),
        ]
      ),
      'sonstigeKostenGesamt' => new JsonSchemaCalculate(
        'number',
        'round(sum(map(sonstigeKosten, "value.betrag")), 2)',
        [
          'sonstigeKosten' => new JsonSchemaDataPointer('1/sonstigeKosten'),
        ]
      ),
      'sonstigeAusgaben' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'zweck' => new JsonSchemaString(),
          'betrag' => new JsonSchemaMoney(['minimum' => 0]),
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
      'zuschlagsrelevanteKosten' => new JsonSchemaObject([
        'programmabsprachen' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/programmabsprachen',
            'identifier' => 'zuschlagsrelevanteKosten.programmabsprachen',
            'clearing' => [
              'itemLabel' => 'Programmabsprachen (Telefon, Porto, Kopien, Internet etc.)',
            ],
          ]),
        ]),
        'vorbereitungsmaterial' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/vorbereitungsmaterial',
            'identifier' => 'zuschlagsrelevanteKosten.vorbereitungsmaterial',
            'clearing' => [
              'itemLabel' => 'Erstellung von Vorbereitungsmaterial',
            ],
          ]),
        ]),
        'veroeffentlichungen' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/veroeffentlichungen',
            'identifier' => 'zuschlagsrelevanteKosten.veroeffentlichungen',
            'clearing' => [
              'itemLabel' => 'Veröffentlichungen, Publikationen, Videos, Fotos etc. als
              Dokumentation der Ergebnisse und für die Öffentlichkeitsarbeit',
            ],
          ]),
        ]),
        'honorare' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/honorare',
            'identifier' => 'zuschlagsrelevanteKosten.honorare',
            'clearing' => [
              'itemLabel' => 'Honorare für Vorträge, die der Vorbereitung der Gruppe dienen (nur im Inland)',
            ],
          ]),
        ]),
        'fahrtkostenUndVerpflegung' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/fahrtkostenUndVerpflegung',
            'identifier' => 'zuschlagsrelevanteKosten.fahrtkostenUndVerpflegung',
            'clearing' => [
              'itemLabel' => 'Fahrtkosten und Verpflegung, ggf. Übernachtung bei überregionaler TN-Zusammensetzung',
            ],
          ]),
        ]),
        'reisekosten' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/reisekosten',
            'identifier' => 'zuschlagsrelevanteKosten.reisekosten',
            'clearing' => [
              'itemLabel' => 'Reise-/Fahrtkosten für interne Koordination und Organisation der Vor- und Nachbereitung',
            ],
          ]),
        ]),
        'miete' => new JsonSchemaMoney([
          'minimum' => 0,
          'default' => 0,
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'zuschlagsrelevanteKosten/miete',
            'identifier' => 'zuschlagsrelevanteKosten.miete',
            'clearing' => [
              'itemLabel' => 'Raum-, Materialmiete (techn. Geräte, Beamer, Flipchart etc.)',
            ],
          ]),
        ]),
      ]),
      'zuschlagsrelevanteKostenGesamt' => new JsonSchemaCalculate(
        'number',
        'begegnungsland == "partnerland" ? round(programmabsprachen + vorbereitungsmaterial + veroeffentlichungen
        + honorare + fahrtkostenUndVerpflegung + reisekosten + miete, 2) : 0',
        [
          'begegnungsland' => new JsonSchemaDataPointer('/grunddaten/begegnungsland', ''),
          'programmabsprachen' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKosten/programmabsprachen'),
          'vorbereitungsmaterial' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKosten/vorbereitungsmaterial'),
          'veroeffentlichungen' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKosten/veroeffentlichungen'),
          'honorare' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKosten/honorare'),
          'fahrtkostenUndVerpflegung' => new JsonSchemaDataPointer(
            '1/zuschlagsrelevanteKosten/fahrtkostenUndVerpflegung'
          ),
          'reisekosten' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKosten/reisekosten'),
          'miete' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKosten/miete'),
        ]
      ),
      'kostenGesamt' => new JsonSchemaCalculate(
        'number',
        'round(unterkunftUndVerpflegung + honorareGesamt
         + fahrtkostenGesamt + programmkostenGesamt + sonstigeKostenGesamt
         + sonstigeAusgabenGesamt + zuschlagsrelevanteKostenGesamt, 2)',
        [
          'unterkunftUndVerpflegung' => new JsonSchemaDataPointer('1/unterkunftUndVerpflegung'),
          'honorareGesamt' => new JsonSchemaDataPointer('1/honorareGesamt'),
          'fahrtkostenGesamt' => new JsonSchemaDataPointer('1/fahrtkostenGesamt'),
          'programmkostenGesamt' => new JsonSchemaDataPointer('1/programmkostenGesamt'),
          'sonstigeKostenGesamt' => new JsonSchemaDataPointer('1/sonstigeKostenGesamt'),
          'sonstigeAusgabenGesamt' => new JsonSchemaDataPointer('1/sonstigeAusgabenGesamt'),
          'zuschlagsrelevanteKostenGesamt' => new JsonSchemaDataPointer('1/zuschlagsrelevanteKostenGesamt'),
        ]
      ),
    ];

    $required = [
      'honorare',
      'fahrtkosten',
      'programmkosten',
      'sonstigeKosten',
      'sonstigeAusgaben',
      'zuschlagsrelevanteKosten',
    ];

    parent::__construct($properties, ['required' => $required]);
  }

}
