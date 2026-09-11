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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema;

use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class KursJsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<int, string> $possibleRecipients
   *   Map of contact IDs to names.
   */
  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    array $possibleRecipients,
    float $festbetragReisekosten,
    float $festbetragTeilnahmetag,
    float $festbetragHonorartag,
  ) {
    $properties = [
      'grunddaten' => new KursGrunddatenSchema($applicationBegin, $applicationEnd),
      'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      // Abschnitt I
      'kosten' => new KursKostenSchema(),
      // Abschnitt II
      'finanzierung' => new KursFinanzierungSchema($festbetragReisekosten, $festbetragTeilnahmetag, $festbetragHonorartag),
      // Beschreibung des Vorhabens (not part of default "AV-K1")
      'beschreibung' => new KursBeschreibungSchema(),
      'projektunterlagen' => new JsonSchemaArray(new JsonSchemaObject([
        '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
        'datei' => new JsonSchemaString(['format' => 'uri']),
        'beschreibung' => new JsonSchemaString(),
      ], ['required' => ['datei', 'beschreibung']])),
    ];

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
