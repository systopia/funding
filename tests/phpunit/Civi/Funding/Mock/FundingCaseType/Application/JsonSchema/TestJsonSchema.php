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

namespace Civi\Funding\Mock\FundingCaseType\Application\JsonSchema;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItem;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Webmozart\Assert\Assert;

final class TestJsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<string, \Civi\RemoteTools\JsonSchema\JsonSchema> $extraProperties
   */
  public function __construct(bool $withRecipient, array $extraProperties = [], array $keywords = []) {
    $required = $keywords['required'] ?? [];
    Assert::isArray($required);
    if ($withRecipient) {
      $required[] = 'recipient';
    }
    $keywords['required'] = array_merge([
      'title',
      'startDate',
      'endDate',
      'amountRequested',
      'resources',
      'file',
    ], $required);

    $properties = [
      'title' => new JsonSchemaString([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'title']]),
      ]),
      'shortDescription' => new JsonSchemaString([
        '$default' => 'Default description',
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'short_description']]),
      ]),
      'startDate' => new JsonSchemaDate([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'start_date']]),
      ]),
      'endDate' => new JsonSchemaDate([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'end_date']]),
      ]),
      'amountRequested' => new JsonSchemaMoney([
        '$costItem' => new JsonSchemaCostItem([
          'type' => 'testCost',
          'identifier' => 'amountRequested',
          'clearing' => [
            'itemLabel' => 'Amount requested',
          ],
        ]),
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'amount_requested']]),
      ]),
      'resources' => new JsonSchemaMoney([
        '$resourcesItem' => new JsonSchemaResourcesItem([
          'type' => 'testResources',
          'identifier' => 'resources',
          'clearing' => [
            'itemLabel' => 'Test Resources',
          ],
        ]),
      ]),
      'file' => new JsonSchemaString(['format' => 'uri']),
    ];

    if ($withRecipient) {
      $properties['recipient'] = new JsonSchemaInteger([
        '$tag' => JsonSchema::fromArray(['mapToField' => ['fieldName' => 'recipient_contact_id']]),
      ]);
    }

    parent::__construct($properties + $extraProperties, $keywords);
  }

}
