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

namespace Civi\Funding\FundingCaseTypes\AdB\SammelantragKurs\Report\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class KursSachberichtJsonSchema extends JsonSchemaObject {

  public function __construct() {
    parent::__construct([
      'inhalteUndZiele' => new JsonSchemaString(['minLength' => 1500]),
      'zielgruppe' => new JsonSchemaString(['minLength' => 1]),
      'erfahrungen' => new JsonSchemaString(['minLength' => 1500]),
      'einbeziehungTeilnehmende' => new JsonSchemaString(['minLength' => 1]),
      'inklusivesVorgehen' => new JsonSchemaString(['minLength' => 1]),
      'ergebnisse' => new JsonSchemaString(['minLength' => 600]),
      'kursinhalte' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 200]),
    ], [
      'required' => [
        'inhalteUndZiele',
        'zielgruppe',
        'erfahrungen',
        'inklusivesVorgehen',
        'ergebnisse',
        'kursinhalte',
      ],
    ]);
  }

}
