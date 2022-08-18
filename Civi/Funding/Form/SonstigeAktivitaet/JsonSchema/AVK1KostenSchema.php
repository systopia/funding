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
      'unterkunftUndVerpflegung' => new JsonSchemaMoney(),
      // Abschnitt I.2
      'honorare' => new JsonSchemaArray(
        new JsonSchemaObject([
          'stunden' => new JsonSchemaNumber(['precision' => 2]),
          'verguetung' => new JsonSchemaMoney(),
          'zweck' => new JsonSchemaString(),
          'betrag' => new JsonSchemaCalculate(
            'number',
            'round(stunden * verguetung, 2)',
            [
              'stunden' => new JsonSchemaDataPointer('1/stunden'),
              'verguetung' => new JsonSchemaDataPointer('1/verguetung'),
            ]
          ),
        ])
      ),
      'honorareGesamt' => new JsonSchemaCalculate('number', 'sum(map(honorare, "value.betrag"))', [
        'honorare' => new JsonSchemaDataPointer('1/honorare'),
      ]),
      // Abschnitt I.6
      'sonstigeAusgaben' => new JsonSchemaArray(
        new JsonSchemaObject([
          'betrag' => new JsonSchemaMoney(),
          'zweck' => new JsonSchemaString(),
        ])
      ),
      'sonstigeAusgabenGesamt' => new JsonSchemaCalculate('number', 'sum(map(sonstigeAusgaben, "value.betrag"))', [
        'sonstigeAusgaben' => new JsonSchemaDataPointer('1/sonstigeAusgaben'),
      ]),
      // Abschnitt I.4
      'fahrtkosten' => new JsonSchemaArray(
        new JsonSchemaObject([
          'betrag' => new JsonSchemaMoney(),
          'zweck' => new JsonSchemaString(),
        ])
      ),
      'fahrtkostenGesamt' => new JsonSchemaCalculate('number', 'sum(map(fahrtkosten, "value.betrag"))', [
        'fahrtkosten' => new JsonSchemaDataPointer('1/fahrtkosten'),
      ]),
      // Gesamtkosten
      'gesamtkosten' => new JsonSchemaCalculate(
        'number',
        'unterkunftUndVerpflegung + honorareGesamt + sonstigeAusgabenGesamt + fahrtkostenGesamt',
        [
          'unterkunftUndVerpflegung' => new JsonSchemaDataPointer('1/unterkunftUndVerpflegung'),
          'honorareGesamt' => new JsonSchemaDataPointer('1/honorareGesamt'),
          'sonstigeAusgabenGesamt' => new JsonSchemaDataPointer('1/sonstigeAusgabenGesamt'),
          'fahrtkostenGesamt' => new JsonSchemaDataPointer('1/fahrtkostenGesamt'),
        ]
      ),
    ]);
  }

}
