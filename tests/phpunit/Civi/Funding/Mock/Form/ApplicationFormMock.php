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

namespace Civi\Funding\Mock\Form;

use Civi\Funding\Form\JsonFormsFormWithData;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class ApplicationFormMock extends JsonFormsFormWithData {

  public function __construct(?JsonSchema $jsonSchema = NULL, ?JsonFormsLayout $uiSchema = NULL, array $data = []) {
    parent::__construct(
      $jsonSchema ?? new JsonSchemaObject(['test' => new JsonSchemaString()]),
      $uiSchema ?? new JsonFormsGroup('Label', [new JsonFormsControl('#/properties/test', 'Test')]),
      $data
    );
  }

}
