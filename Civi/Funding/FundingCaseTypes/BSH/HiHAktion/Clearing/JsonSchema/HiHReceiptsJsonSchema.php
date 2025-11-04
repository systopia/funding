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
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class HiHReceiptsJsonSchema extends JsonSchemaObject {

  public function __construct(
    ApplicationCostItemEntity $personalkostenBewilligt,
    ApplicationCostItemEntity $honorareBewilligt,
    ApplicationCostItemEntity $sachkostenBewilligt,
    ClearingProcessEntityBundle $clearingProcessBundle,
  ) {
    $properties = [
      'costItems' => new HiHClearingCostItemsJsonSchema(
        $personalkostenBewilligt,
        $honorareBewilligt,
        $sachkostenBewilligt
      ),
    ];

    $properties['sachkostenAmountRecordedTotal'] = new JsonSchemaCalculate(
      'number',
      'round(sachkostenAmountRecorded + sachkostenSonstigeAmountRecorded, 2)',
      [
        'sachkostenAmountRecorded' => new JsonSchemaDataPointer('1/costItems/sachkosten/amountRecordedTotal', 0),
        'sachkostenSonstigeAmountRecorded'
        => new JsonSchemaDataPointer('1/costItems/sachkostenSonstige/amountRecordedTotal', 0),
      ]
    );

    $properties['ausgaben'] = new JsonSchemaCalculate(
      'number',
      'round(personalkostenAmount + honorareAmount + sachkostenAmount, 2)',
      [
        'personalkostenAmount' => new JsonSchemaDataPointer('1/costItems/personalkosten/amountRecordedTotal', 0),
        'honorareAmount' => new JsonSchemaDataPointer('1/costItems/honorare/amountRecordedTotal', 0),
        'sachkostenAmount' => new JsonSchemaDataPointer('1/sachkostenAmountRecordedTotal'),
      ],
      NULL,
      [
        '$validations' => JsonSchema::convertToJsonSchemaArray([
          [
            'keyword' => 'maximum',
            'value' => $clearingProcessBundle->getFundingCase()->getAmountApproved(),
            'message' => 'Der bewilligte Betrag darf nicht überschritten werden.',
          ],
        ]),
      ]
    );

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
