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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema;

use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class HiHApplicationJsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<int, string> $possibleRecipients
   *    Map of contact IDs to names.
   */
  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    array $possibleRecipients
  ) {
    // @todo Validate conditional fields.
    // @todo Additional validations, e.g. required, length, min, max, ...
    $properties = [
      'fragenZumProjekt' => new HiHFragenZumProjektJsonSchema(),
      'informationenZumProjekt' => new HiHInformationenZumProjektJsonSchema($applicationBegin, $applicationEnd),
      'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      'kostenUndFinanzierung' => new HiHKostenUndFinanzierungJsonSchema(),
      'rechtliches' => new HiHRechtlichesJsonSchema(),
      'amount' => new JsonSchemaNumber([
        'default' => 0.0,
        '$default' => 0.0,
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']]),
      ]),
    ];

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
