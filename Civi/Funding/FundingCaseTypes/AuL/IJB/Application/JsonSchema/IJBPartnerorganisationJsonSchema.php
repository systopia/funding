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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema;

use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class IJBPartnerorganisationJsonSchema extends JsonSchemaObject {

  public function __construct() {
    $properties = [
      'name' => new JsonSchemaString(),
      'adresse' => new JsonSchemaString(),
      'land' => new JsonSchemaString(),
      'email' => new JsonSchemaString(),
      'telefon' => new JsonSchemaString(),
      'kontaktperson' => new JsonSchemaString(),
      'fortsetzungsmassnahme' => new JsonSchemaBoolean(),
      'konzeptionellNeu' => new JsonSchemaBoolean(['default' => FALSE]),
      'austauschSeit' => new JsonSchemaString(),
      'bisherigeBegegnungenInDeutschland' => new JsonSchemaString(),
      'bisherigeBegegnungenImPartnerland' => new JsonSchemaString(),
    ];

    $required = [
      'name',
      'adresse',
      'land',
      'email',
      'telefon',
      'kontaktperson',
    ];

    parent::__construct($properties, ['required' => $required]);
  }

}
