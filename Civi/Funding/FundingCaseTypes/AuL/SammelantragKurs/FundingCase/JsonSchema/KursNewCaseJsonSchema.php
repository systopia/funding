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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\FundingCase\JsonSchema;

use Civi\Funding\Form\JsonSchema\JsonSchemaRecipient;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Webmozart\Assert\Assert;

final class KursNewCaseJsonSchema extends JsonSchemaObject {

  /**
   * @phpstan-param array<int, string> $possibleRecipients
   *   Map of contact IDs to names.
   * @phpstan-param array<string, \Civi\RemoteTools\JsonSchema\JsonSchema> $extraProperties
   */
  public function __construct(array $possibleRecipients, array $extraProperties = [], array $keywords = []) {
    $required = $keywords['required'] ?? [];
    Assert::isArray($required);
    $keywords['required'] = array_merge([
      'empfaenger',
    ], $required);

    parent::__construct(
      [
        'empfaenger' => new JsonSchemaRecipient($possibleRecipients),
      ] + $extraProperties,
      $keywords,
    );
  }

}
