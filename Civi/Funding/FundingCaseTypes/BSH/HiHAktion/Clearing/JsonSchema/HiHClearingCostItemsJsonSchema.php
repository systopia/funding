<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\JsonSchema;

use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class HiHClearingCostItemsJsonSchema extends JsonSchemaObject {

  public function __construct(
    ApplicationCostItemEntity $personalkostenBewilligt,
    ApplicationCostItemEntity $honorareBewilligt,
    ApplicationCostItemEntity $sachkostenBewilligt
  ) {
    $sachkostenKeys = [
      'materialien',
      'ehrenamtspauschalen',
      'verpflegung',
      'fahrtkosten',
      'investitionen',
      'mieten',
    ];

    $sachkostenRecords = [];
    foreach ($sachkostenKeys as $sachkostenKey) {
      $sachkostenRecords[$sachkostenKey] = new JsonSchemaObject([
        '_id' => new JsonSchemaInteger(['default' => NULL], TRUE),
        '_financePlanItemId' => new JsonSchemaInteger([
          'const' => $sachkostenBewilligt->getId(),
          'default' => $sachkostenBewilligt->getId(),
        ]),
        'amount' => new JsonSchemaMoney(['default' => 0]),
        'amountAdmitted' => new JsonSchemaMoney([], TRUE),
      ], ['required' => ['_financePlanItemId', 'amount']]);
    }

    $properties = [
      'personalkosten' => new JsonSchemaObject([
        'records' => new JsonSchemaArray(new JsonSchemaObject([
          '_id' => new JsonSchemaInteger(['default' => NULL], TRUE),
          '_financePlanItemId' => new JsonSchemaInteger([
            'const' => $personalkostenBewilligt->getId(),
            'default' => $personalkostenBewilligt->getId(),
          ]),
          'properties' => new JsonSchemaObject([
            'posten' => new JsonSchemaString(['minLength' => 1]),
            'wochenstunden' => new JsonSchemaInteger(),
            'monatlichesArbeitgeberbrutto' => new JsonSchemaMoney(),
            'monate' => new JsonSchemaInteger(),
          ], ['required' => ['posten', 'wochenstunden', 'monatlichesArbeitgeberbrutto', 'monate']]),
          'amount' => new JsonSchemaCalculate('number', 'round(monatlichesArbeitgeberbrutto * monate, 2)', [
            'monatlichesArbeitgeberbrutto' => new JsonSchemaDataPointer('1/properties/monatlichesArbeitgeberbrutto'),
            'monate' => new JsonSchemaDataPointer('1/properties/monate'),
          ]),
          'amountAdmitted' => new JsonSchemaMoney([], TRUE),
        ], ['required' => ['amount', 'properties']])),
        'amountRecordedTotal' => new JsonSchemaCalculate(
          'number',
          'round(sum(map(records, "value.amount ?: 0")), 2)',
          ['records' => new JsonSchemaDataPointer('1/records')],
          NULL,
          ['default' => 0]
        ),
      ], ['required' => ['records']]),

      'honorare' => new JsonSchemaObject([
        'records' => new JsonSchemaArray(new JsonSchemaObject([
          '_id' => new JsonSchemaInteger(['default' => NULL], TRUE),
          '_financePlanItemId' => new JsonSchemaInteger([
            'const' => $honorareBewilligt->getId(),
            'default' => $honorareBewilligt->getId(),
          ]),
          'properties' => new JsonSchemaObject([
            'posten' => new JsonSchemaString(['minLength' => 1]),
            'berechnungsgrundlage' => new JsonSchemaString([
              'oneOf' => JsonSchemaUtil::buildTitledOneOf([
                'stundensatz' => 'Stundensatz',
                'tagessatz' => 'Tagessatz',
              ]),
            ]),
            'verguetung' => new JsonSchemaMoney(['minimum' => 0]),
            'dauer' => new JsonSchemaNumber(['precision' => 2, 'minimum' => 0]),
          ], ['required' => ['posten', 'berechnungsgrundlage', 'verguetung', 'dauer']]),
          'amount' => new JsonSchemaCalculate('number', 'round(dauer * verguetung, 2)', [
            'dauer' => new JsonSchemaDataPointer('1/properties/dauer'),
            'verguetung' => new JsonSchemaDataPointer('1/properties/verguetung'),
          ]),
          'amountAdmitted' => new JsonSchemaMoney([], TRUE),
        ], ['required' => ['amount', 'properties']])),
        'amountRecordedTotal' => new JsonSchemaCalculate(
          'number',
          'round(sum(map(records, "value.amount ?: 0")), 2)',
          ['records' => new JsonSchemaDataPointer('1/records')],
          NULL,
          ['default' => 0]
        ),
      ], ['required' => ['records']]),

      'sachkosten' => new JsonSchemaObject([
        'records' => new JsonSchemaObject($sachkostenRecords, ['required' => $sachkostenKeys]),
        'amountRecordedTotal' => new JsonSchemaCalculate(
          'number',
          'round(' . implode('+', array_map(fn ($key) => "(records.$key.amount ?: 0)", $sachkostenKeys)) . ', 2)',
          ['records' => new JsonSchemaDataPointer('1/records')],
        ),
      ], ['required' => ['records']]),

      'sachkostenSonstige' => new JsonSchemaObject([
        'records' => new JsonSchemaArray(new JsonSchemaObject([
          '_id' => new JsonSchemaInteger(['default' => NULL], TRUE),
          '_financePlanItemId' => new JsonSchemaInteger([
            'const' => $sachkostenBewilligt->getId(),
            'default' => $sachkostenBewilligt->getId(),
          ]),
          'properties' => new JsonSchemaObject([
            'bezeichnung' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
          ], ['required' => ['bezeichnung']]),
          'amount' => new JsonSchemaMoney(),
          'amountAdmitted' => new JsonSchemaMoney([], TRUE),
        ], ['required' => ['_financePlanItemId', 'amount', 'properties']])),
        'amountRecordedTotal' => new JsonSchemaCalculate(
          'number',
          'round(sum(map(records, "value.amount ?: 0")), 2)',
          ['records' => new JsonSchemaDataPointer('1/records')],
          NULL,
          ['default' => 0]
        ),
      ], ['required' => ['records']]),
    ];

    $keywords = ['required' => array_keys($properties)];

    parent::__construct($properties, $keywords);
  }

}
