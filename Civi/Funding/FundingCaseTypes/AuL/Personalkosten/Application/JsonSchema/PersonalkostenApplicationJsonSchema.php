<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class PersonalkostenApplicationJsonSchema extends JsonSchemaObject {

  /**
   * @param array<int, string> $possibleRecipients
   *    Map of contact IDs to names.
   * @param list<string> $limitedValidationActions
   * @param bool $report
   *    TRUE if used for clearing report.
   */
  public function __construct(
    int $foerderquote,
    float $sachkostenpauschale,
    \DateTimeInterface $applicationBegin,
    \DateTimeInterface $applicationEnd,
    array $possibleRecipients,
    array $limitedValidationActions,
    bool $report = FALSE
  ) {
    $properties = [
      'name' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 100, '$limitValidation' => FALSE]),
      'vorname' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 100, '$limitValidation' => FALSE]),
      'tarifUndEingruppierung' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
      'titel' => new JsonSchemaCalculate(
        'string',
        '"Personalkostenförderung " ~ vorname ~ " " ~ name',
        [
          'vorname' => new JsonSchemaDataPointer('1/vorname'),
          'name' => new JsonSchemaDataPointer('1/name'),
        ],
        NULL,
        [
          'minLength' => 1,
          'maxLength' => 255,
          '$limitValidation' => FALSE,
          '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'title']]),
        ]
      ),
      'kurzbeschreibung' => new JsonSchemaCalculate(
        'string',
        '"Personalkostenförderung " ~ vorname ~ " " ~ name',
        [
          'vorname' => new JsonSchemaDataPointer('1/vorname'),
          'name' => new JsonSchemaDataPointer('1/name'),
        ],
        NULL,
        [
          'minLength' => 1,
          'maxLength' => 255,
          '$limitValidation' => FALSE,
          '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'short_description']]),
        ]
      ),
      'beginn' => new JsonSchemaDate([
        'minDate' => $applicationBegin->format('Y-m-d'),
        'maxDate' => $applicationEnd->format('Y-m-d'),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'start_date']]),
      ]),
      'ende' => new JsonSchemaDate([
        'minDate' => new JsonSchemaDataPointer('1/beginn', '0000-00-00'),
        'maxDate' => $applicationEnd->format('Y-m-d'),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'end_date']]),
      ]),
      'personalkostenTatsaechlich' => new JsonSchemaMoney(['minimum' => 0]),
      'personalkostenBeantragt' => new JsonSchemaMoney([
        'minimum' => 0,
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'personalkosten',
          'identifier' => 'personalkosten',
          'clearing' => [
            'itemLabel' => 'Beantragte Personalkosten',
          ],
        ]),
      ]),
      'sachkostenpauschale' => new JsonSchemaMoney([
        'const' => $sachkostenpauschale,
        'readOnly' => TRUE,
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'sachkostenpauschale',
          'identifier' => 'sachkostenpauschale',
          'clearing' => [
            'itemLabel' => 'Sachkostenpauschale',
          ],
        ]),
      ]),
      // Store in request data to know which Förderquote was applied for application snapshot.
      'foerderquote' => new JsonSchemaInteger([
        'const' => $foerderquote,
        'readOnly' => TRUE,
        'default' => $foerderquote,
      ]),
      'beantragterZuschuss' => new JsonSchemaCalculate(
        'number',
        'round(foerderquote * personalkostenBeantragt / 100 + sachkostenpauschale, 2)',
        [
          'foerderquote' => $foerderquote,
          'personalkostenBeantragt' => new JsonSchemaDataPointer('1/personalkostenBeantragt', 0),
          'sachkostenpauschale' => $sachkostenpauschale,
        ],
        0.0,
        ['$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']])]
      ),
      'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      'dokumente' => new PersonalkostenDokumenteJsonSchema(),
    ];

    $required = array_keys($properties);

    if ($report) {
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

    parent::__construct($properties, [
      'required' => $required,
      '$limitValidation' => JsonSchema::fromArray([
        'condition' => [
          'evaluate' => [
            'expression' => 'action in limitedValidationActions',
            'variables' => [
              'action' => new JsonSchemaDataPointer('/_action', ''),
              'limitedValidationActions' => $limitedValidationActions,
            ],
          ],
          'schema' => [
            'required' => ['name', 'vorname', 'empfaenger'],
          ],
        ],
      ]),
    ]);
  }

}
