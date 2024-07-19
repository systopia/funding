<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\SonstigeAktivitaet\Application\JsonSchema;

use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

/**
 * This implements the JSON schema for an "AV-K1" form to apply for a funding
 * for a "Sonstige Aktivität" in the "Kinder- und Jugendplan des Bundes (KJP)".
 * Because it is a specific German form strings are not translated.
 */
final class AVK1JsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<int, string> $possibleRecipients
   *   Map of contact IDs to names.
   */
  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    array $possibleRecipients
  ) {
    // TODO: Additional validations (required, length, min, max, ...)
    $properties = [
      'grunddaten' => new AVK1GrunddatenSchema($applicationBegin, $applicationEnd),
      'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      // Abschnitt I
      'kosten' => new AVK1KostenSchema(),
      // Abschnitt II
      'finanzierung' => new AVK1FinanzierungSchema(),
      // Beschreibung des Vorhabens (not part of default "AV-K1")
      'beschreibung' => new AVK1BeschreibungSchema(),
      'projektunterlagen' => new JsonSchemaArray(new JsonSchemaObject([
        '_identifier' => new JsonSchemaString(['readonly' => TRUE]),
        'datei' => new JsonSchemaString(['format' => 'uri']),
        'beschreibung' => new JsonSchemaString(),
      ], ['required' => ['datei', 'beschreibung']])),
    ];

    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
