<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema;

use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class PersonalkostenClearingCostItemsJsonSchema extends JsonSchemaObject {

  public function __construct(
    ApplicationCostItemEntity $personalkostenBewilligt,
    ApplicationCostItemEntity $sachkostenpauschaleBewilligt,
    bool $hasClearingChangePermission
  ) {
    $properties = [
      'personalkosten' => new JsonSchemaObject([
        'records' => new JsonSchemaObject([
          'personalkosten' => new JsonSchemaObject([
            '_id' => new JsonSchemaInteger(['default' => NULL], TRUE),
            '_financePlanItemId' => new JsonSchemaInteger([
              'const' => $personalkostenBewilligt->getId(),
              'default' => $personalkostenBewilligt->getId(),
            ]),
            'amount' => new JsonSchemaMoney(['default' => $personalkostenBewilligt->getAmount()]),
            'amountAdmitted' => new JsonSchemaMoney(['maximum' => new JsonSchemaDataPointer('1/amount')], TRUE),
          ], ['required' => ['_financePlanItemId', 'amount']]),
        ], ['required' => ['personalkosten']]),
      ], ['required' => ['records']]),
      'sachkostenpauschale' => new JsonSchemaObject([
        'records' => new JsonSchemaObject([
          'sachkostenpauschale' => new JsonSchemaObject([
            '_id' => new JsonSchemaInteger(['default' => NULL], TRUE),
            '_financePlanItemId' => new JsonSchemaInteger([
              'const' => $sachkostenpauschaleBewilligt->getId(),
              'default' => $sachkostenpauschaleBewilligt->getId(),
            ]),
            'amount' => new JsonSchemaMoney(['default' => $sachkostenpauschaleBewilligt->getAmount(), 'readOnly' => TRUE]),
            'amountAdmitted' => new JsonSchemaMoney(['maximum' => new JsonSchemaDataPointer('1/amount')], TRUE),
          ], ['required' => ['_financePlanItemId', 'amount']]),
        ], ['required' => ['sachkostenpauschale']]),
      ], ['required' => ['records'], 'readOnly' => TRUE]),
    ];
    if (!$hasClearingChangePermission) {
      foreach ($properties as $schema) {
        $schema['readOnly'] = TRUE;
      }
    }

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
