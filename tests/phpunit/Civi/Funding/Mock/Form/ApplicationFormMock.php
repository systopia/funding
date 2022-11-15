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

use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonForms\JsonFormsLayout;
use Civi\RemoteTools\Form\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\Form\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\Form\RemoteForm;

final class ApplicationFormMock extends RemoteForm {

  public function __construct(?JsonSchema $jsonSchema = NULL, ?JsonFormsLayout $uiSchema = NULL, array $data = []) {
    parent::__construct(
      $jsonSchema ?? new JsonSchemaObject(['test' => new JsonSchemaString()]),
      $uiSchema ?? new JsonFormsGroup('Label', [new JsonFormsControl('#/properties/test', 'Test')]),
      $data
    );
  }

}
