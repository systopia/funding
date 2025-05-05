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

namespace Civi\Funding\IJB\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;

final class IJBGrunddatenJsonSchema extends JsonSchemaObject {

  public const ART_DER_MASSNHAME_OPTIONS = [
    'fachkraefteprogramm' => 'Fachkräfteprogramm',
    'jugendbegegnung' => 'Jugendbegegnung oder Workcamp',
  ];

  /**
   * @param bool $report TRUE if used for report.
   */
  public function __construct(
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    bool $report = FALSE
  ) {
    $properties = [
      'titel' => new JsonSchemaString([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'title']]),
      ]),
      'kurzbeschreibungDesInhalts' => new JsonSchemaString([
        'maxLength' => 500,
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'short_description']]),
      ]),
      'zeitraeume' => new JsonSchemaArray(
        new JsonSchemaObject([
          'beginn' => new JsonSchemaDate([
            'minDate' => $applicationBegin->format('Y-m-d'),
            'maxDate' => $applicationEnd->format('Y-m-d'),
            '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'start_date', 'replace' => FALSE]]),
          ]),
          'ende' => new JsonSchemaDate([
            'minDate' => new JsonSchemaDataPointer('1/beginn', '0000-00-00'),
            'maxDate' => $applicationEnd->format('Y-m-d'),
            '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'end_date']]),
          ]),
        ],
          [
            'required' => ['beginn', 'ende'],
          ]),
        [
          'minItems' => 1,
          'noIntersect' => JsonSchema::fromArray(['begin' => 'beginn', 'end' => 'ende']),
          '$order' => JsonSchema::fromArray(['beginn' => 'ASC']),
        ]
      ),
      'programmtage' => new JsonSchemaCalculate(
        'number',
        <<<'EOD'
sum(
  map(zeitraeume, "
    value.beginn && value.ende
    ? date_create(value.beginn).diff(date_create(value.ende)).days + 1
    : 0
  ")
)
EOD,
        ['zeitraeume' => new JsonSchemaDataPointer('1/zeitraeume')],
        0
      ),
      'artDerMassnahme' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf(self::ART_DER_MASSNHAME_OPTIONS),
      ]),
      'begegnungsland' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf([
          'deutschland' => 'Deutschland',
          'partnerland' => 'Partnerland',
        ]),
      ]),
      'stadt' => new JsonSchemaString(),
      'land' => new JsonSchemaString(),
      'fahrtstreckeInKm' => new JsonSchemaInteger(['default' => 0]),
    ];

    if ($report) {
      $properties['programmtageMitHonorar'] = new JsonSchemaInteger([
        'minimum' => 0,
        'maximum' => new JsonSchemaDataPointer('1/programmtage'),
      ], TRUE);
    }

    $required = array_filter(
      array_keys($properties),
      static fn (string $key) => $key !== 'fahrtstreckeInKm' && $key !== 'programmtageMitHonorar',
    );

    parent::__construct($properties, ['required' => $required]);
  }

}
