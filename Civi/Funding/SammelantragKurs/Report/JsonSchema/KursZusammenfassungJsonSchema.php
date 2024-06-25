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

namespace Civi\Funding\SammelantragKurs\Report\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class KursZusammenfassungJsonSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      // The referenced field might not exist, if there's no resource item with
      // that type. Thus, we need to use these calculations with fallbacks that
      // can be referenced in the UI schema.
      'amountRecorded_sonstigeMittel' => new JsonSchemaCalculate('number', 'value', [
        'value' => new JsonSchemaDataPointer('/resourcesItemsByType/amountRecorded_sonstigeMittel', 0),
      ]),
      'amountAdmitted_sonstigeMittel' => new JsonSchemaCalculate('number', 'value', [
        'value' => new JsonSchemaDataPointer('/resourcesItemsByType/amountAdmitted_sonstigeMittel', 0),
      ]),

      // Use "calculated" fields for the referenced values so they get updated
      // if the value in the input field is changed.
      'foerderungTeilnahmetage' => new JsonSchemaCalculate('number', 'value', [
        'value' => new JsonSchemaDataPointer('2/foerderung/teilnahmetage', 0),
      ]),
      'foerderungHonorare' => new JsonSchemaCalculate('number', 'value', [
        'value' => new JsonSchemaDataPointer('2/foerderung/honorare', 0),
      ]),
      'foerderungFahrtkosten' => new JsonSchemaCalculate('number', 'value', [
        'value' => new JsonSchemaDataPointer('2/foerderung/fahrtkosten', 0),
      ]),
    ]);
  }

}
