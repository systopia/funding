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
    $ifFullValidation = JsonSchema::fromArray([
      'evaluate' => [
        'expression' => 'action != "save"',
        'variables' => ['action' => ['$data' => '/_action', 'fallback' => '']],
      ],
    ]);

    // @todo Additional validations, e.g. required, length, min, max, ...
    $properties = [
      'fragenZumProjekt' => new HiHFragenZumProjektJsonSchema($ifFullValidation),
      'informationenZumProjekt' => new HiHInformationenZumProjektJsonSchema(
        $applicationBegin, $applicationEnd, $ifFullValidation
      ),
      'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      'kosten' => new HiHKostenJsonSchema($ifFullValidation),
      'finanzierung' => new HiHFinanzierungJsonSchema($ifFullValidation),
      'rechtliches' => new HiHRechtlichesJsonSchema($ifFullValidation),
    ];

    parent::__construct($properties, [
      'required' => [
        'fragenZumProjekt',
        'informationenZumProjekt',
        'empfaenger',
        'kosten',
        'finanzierung',
        'rechtliches',
      ],
    ]);
  }

}
