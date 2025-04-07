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

namespace Civi\Funding\SammelantragKurs\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class KursGrunddatenJsonSchema extends JsonSchemaObject {

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
      'kurzbeschreibungDerInhalte' => new JsonSchemaString([
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
      'teilnehmer' => new JsonSchemaObject([
        'gesamt' => new JsonSchemaInteger(['minimum' => 1]),
        'weiblich' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'divers' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'unter27' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeEhrenamtlichTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'inJugendhilfeHauptamtlichTaetig' => new JsonSchemaInteger(['minimum' => 0], TRUE),
        'referenten' => new JsonSchemaInteger(['minimum' => 0]),
      ], ['required' => ['gesamt', 'referenten']]),
      'teilnehmertage' => new JsonSchemaCalculate(
        'number',
        'programmtage * teilnehmerGesamt',
        [
          'programmtage' => new JsonSchemaDataPointer('1/programmtage'),
          'teilnehmerGesamt' => new JsonSchemaDataPointer('1/teilnehmer/gesamt'),
        ],
        0
      ),
    ];

    if ($report) {
      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $teilnehmerProperties */
      $teilnehmerProperties = $properties['teilnehmer']['properties'];
      $teilnehmerProperties['referentenMitHonorar'] = new JsonSchemaInteger(['minimum' => 0], TRUE);
      $teilnehmerProperties['mitFahrtkosten'] = new JsonSchemaInteger(['minimum' => 0], TRUE);

      $properties['internerBezeichner'] = new JsonSchemaString([
        'maxLength' => 255,
        'readOnly' => TRUE,
      ]);
    }
    else {
      $properties['internerBezeichner'] = new JsonSchemaString([
        'maxLength' => 255,
        '$tag' => JsonSchema::fromArray(
            ['mapToField' => ['fieldName' => 'funding_application_process_extra.internal_identifier']]
        ),
      ]);
    }

    $required = array_filter(
      array_keys($properties),
      static fn (string $key) => $key !== 'internerBezeichner',
    );

    parent::__construct($properties, ['required' => $required]);
  }

  /**
   * In report all fields are required.
   */
  public function withAllFieldsRequired(): self {
    $schema = clone $this;
    $schema->addValidations();

    return $schema;
  }

  private function addValidations(): void {
    $requiredTeilnehmerIntegers = [
      'weiblich',
      'divers',
      'unter27',
      'inJugendhilfeEhrenamtlichTaetig',
      'inJugendhilfeHauptamtlichTaetig',
      'referenten',
      'referentenMitHonorar',
      'mitFahrtkosten',
    ];

    // @phpstan-ignore-next-line
    $this['properties']['teilnehmer']['required']
    // @phpstan-ignore-next-line
      = array_merge($this['properties']['teilnehmer']['required'], $requiredTeilnehmerIntegers);

    foreach ($requiredTeilnehmerIntegers as $property) {
      // @phpstan-ignore-next-line
      $this['properties']['teilnehmer']['properties'][$property]['type'] = 'integer';
    }
  }

}
