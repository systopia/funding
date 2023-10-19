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

namespace Civi\Funding\IJB\Application\JsonSchema;

use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Webmozart\Assert\Assert;

final class IJBApplicationJsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<int, string> $possibleRecipients
   *    Map of contact IDs to names.
   * @phpstan-param array<string, \Civi\RemoteTools\JsonSchema\JsonSchema> $extraProperties
   */
  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    array $possibleRecipients,
    array $extraProperties = [],
    array $keywords = []
  ) {
    // @todo Additional validations, e.g. required, length, min, max, ...
    $properties = [
      'grunddaten' => new IJBGrunddatenJsonSchema($applicationBegin, $applicationEnd),
      'teilnehmer' => new IJBTeilnehmerJsonSchema(),
      'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      'partnerorganisation' => new IJBPartnerorganisationJsonSchema(),
      'kosten' => new IJBKostenJsonSchema(),
      'finanzierung' => new IJBFinanzierungJsonSchema(),
      'zuschuss' => new IJBZuschussJsonSchema(),
      'beschreibung' => new IJBBeschreibungJsonSchema(),
    ];

    $required = $keywords['required'] ?? [];
    Assert::isArray($required);
    $keywords['required'] = array_merge(array_keys($properties), $required);

    parent::__construct($properties + $extraProperties, $keywords);
  }

}
