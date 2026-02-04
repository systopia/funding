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

namespace Civi\Funding\Form\Application;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\JsonSchema\JsonSchema;

interface ApplicationJsonSchemaFactoryInterface {

  public const SERVICE_TAG = 'funding.application.json_schema_factory';

  /**
   * @phpstan-return list<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * Called for an existing application process.
   *
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *   Status of other application processes in same funding case indexed by ID.
   */
  public function createJsonSchemaExisting(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList
  ): JsonSchema;

  /**
   * The returned schema is used to extract translatable strings. If there are
   * strings with dynamic content, they can be replaced by a JsonSchema object
   * with the keywords "text" and "values", and optionally "locale".
   * The keyword "text" contains the string with placeholders, and "values" a
   * mapping of placeholder to replacement, e.g. (JSON encoded):
   * { "text": "Hello {name}", "values": {"name": "World"} }
   *
   * For the replacement PHP's \MessageFormatter is used. This allows locale
   * aware number and date formatting. If the property "locale" is not given,
   * CiviCRMs locale will be used.
   *
   * If no translation is required, an empty schema can be returned.
   *
   * @see \Civi\Funding\Translation\JsonSchemaStringExtractor
   * For the places where strings are extracted.
   *
   * @see \MessageFormatter::formatMessage()
   */
  public function createJsonSchemaForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType,
  ): JsonSchema;

}
