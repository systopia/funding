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

use Civi\Funding\Form\JsonSchema\JsonSchemaArray;
use Civi\Funding\Form\JsonSchema\JsonSchemaCalculate;
use Civi\Funding\Form\JsonSchema\JsonSchemaMoney;
use Civi\Funding\Form\JsonSchema\JsonSchemaObject;
use Civi\Funding\Form\JsonSchema\JsonSchemaDataPointer;
use Civi\Funding\Form\JsonSchema\JsonSchemaString;

final class AVK1FinanzierungSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      // Abschnitt II.1
      'teilnehmerbeitraege' => new JsonSchemaMoney(),
      // Abschnitt II.2
      'eigenmittel' => new JsonSchemaMoney(),
      // Abschnitt II.3
      'oeffentlicheMittel' => new JsonSchemaObject([
        'europa' => new JsonSchemaMoney(),
        'bundeslaender' => new JsonSchemaMoney(),
        'staedteUndKreise' => new JsonSchemaMoney(),
      ]),
      'oeffentlicheMittelGesamt' => new JsonSchemaCalculate('number', 'europa + bundeslaender + staedteUndKreise', [
        'europa' => new JsonSchemaDataPointer('1/oeffentlicheMittel/europa'),
        'bundeslaender' => new JsonSchemaDataPointer('1/oeffentlicheMittel/bundeslaender'),
        'staedteUndKreise' => new JsonSchemaDataPointer('1/oeffentlicheMittel/staedteUndKreise'),
      ]),
      // Abschnitt II.4
      'sonstigeMittel' => new JsonSchemaArray(
        new JsonSchemaObject([
          'betrag' => new JsonSchemaMoney(),
          'quelle' => new JsonSchemaString(),
        ])
      ),
      'sonstigeMittelGesamt' => new JsonSchemaCalculate('number', 'sum(map(sonstigeMittel, "value.betrag"))', [
        'sonstigeMittel' => new JsonSchemaDataPointer('1/sonstigeMittel'),
      ]),
      // Gesamtmittel ohne Zuschuss
      'gesamtmittel' => new JsonSchemaCalculate(
        'number',
        'teilnehmerbeitraege + eigenmittel + oeffentlicheMittelGesamt + sonstigeMittelGesamt',
        [
          'teilnehmerbeitraege' => new JsonSchemaDataPointer('1/teilnehmerbeitraege'),
          'eigenmittel' => new JsonSchemaDataPointer('1/eigenmittel'),
          'oeffentlicheMittelGesamt' => new JsonSchemaDataPointer('1/oeffentlicheMittelGesamt'),
          'sonstigeMittelGesamt' => new JsonSchemaDataPointer('1/sonstigeMittelGesamt'),
        ]
      ),
      // Beantragter Zuschuss
      'beantragterZuschuss' => new JsonSchemaCalculate('number', 'max(gesamtkosten - gesamtmittel, 0)', [
        'gesamtkosten' => new JsonSchemaDataPointer('/kosten/gesamtkosten'),
        'gesamtmittel' => new JsonSchemaDataPointer('1/gesamtmittel'),
      ]),
    ]);
  }

}
