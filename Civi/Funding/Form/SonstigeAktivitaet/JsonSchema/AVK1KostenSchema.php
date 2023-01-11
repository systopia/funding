<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\RemoteTools\Form\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaString;

final class AVK1KostenSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      // Abschnitt I.1
      'unterkunftUndVerpflegung' => new JsonSchemaMoney(['minimum' => 0]),
      // Abschnitt I.2
      'honorare' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'stunden' => new JsonSchemaNumber(['precision' => 2]),
          'verguetung' => new JsonSchemaMoney(['minimum' => 0]),
          'zweck' => new JsonSchemaString(),
          'betrag' => new JsonSchemaCalculate(
            'number',
            'round(stunden * verguetung, 2)',
            [
              'stunden' => new JsonSchemaDataPointer('1/stunden'),
              'verguetung' => new JsonSchemaDataPointer('1/verguetung'),
            ]
          ),
        ], ['required' => ['stunden', 'verguetung', 'zweck']])
      ),
      'honorareGesamt' => new JsonSchemaCalculate('number', 'round(sum(map(honorare, "value.betrag")), 2)', [
        'honorare' => new JsonSchemaDataPointer('1/honorare'),
      ]),
      // Abschnitt I.4
      'fahrtkosten' => new JsonSchemaObject([
        'intern' => new JsonSchemaMoney(['minimum' => 0]),
        'anTeilnehmerErstattet' => new JsonSchemaMoney(['minimum' => 0]),
      ], ['required' => ['intern', 'anTeilnehmerErstattet']]),
      'fahrtkostenGesamt' => new JsonSchemaCalculate('number', 'round(intern + anTeilnehmerErstattet, 2)', [
        'intern' => new JsonSchemaDataPointer('1/fahrtkosten/intern'),
        'anTeilnehmerErstattet' => new JsonSchemaDataPointer('1/fahrtkosten/anTeilnehmerErstattet'),
      ]),
      // Abschnitt I.5
      'sachkosten' => new JsonSchemaObject([
        'haftungKfz' => new JsonSchemaMoney(['minimum' => 0]),
        'ausstattung' => new JsonSchemaArray(
          new JsonSchemaObject([
            '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
            'gegenstand' => new JsonSchemaString(),
            'betrag' => new JsonSchemaMoney(['minimum' => 0]),
          ], ['required' => ['gegenstand', 'betrag']])
        ),
      ], ['required' => ['haftungKfz']]),
      'sachkostenGesamt' => new JsonSchemaCalculate('number', 'round(haftungKfz + sum(map(ausstattung, "value.betrag")), 2)', [
        'haftungKfz' => new JsonSchemaDataPointer('1/sachkosten/haftungKfz'),
        'ausstattung' => new JsonSchemaDataPointer('1/sachkosten/ausstattung'),
      ]),
      // Abschnitt I.6
      'sonstigeAusgaben' => new JsonSchemaArray(
        new JsonSchemaObject([
          '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
          'betrag' => new JsonSchemaMoney(['minimum' => 0]),
          'zweck' => new JsonSchemaString(),
        ], ['required' => ['betrag', 'zweck']])
      ),
      'sonstigeAusgabenGesamt' => new JsonSchemaCalculate('number', 'round(sum(map(sonstigeAusgaben, "value.betrag")), 2)', [
        'sonstigeAusgaben' => new JsonSchemaDataPointer('1/sonstigeAusgaben'),
      ]),
      // Abschnitt I.7
      'versicherungTeilnehmer' => new JsonSchemaMoney(['minimum' => 0, 'default' => 0]),
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
          'versicherungTeilnehmer' => new JsonSchemaDataPointer('1/versicherungTeilnehmer'),
        ]
      ),
    ], [
      'required' => [
        'unterkunftUndVerpflegung',
      ],
    ]);
  }

}
