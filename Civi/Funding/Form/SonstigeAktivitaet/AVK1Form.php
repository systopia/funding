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

namespace Civi\Funding\Form\SonstigeAktivitaet;

use Civi\Funding\Form\FundingForm;
use Civi\Funding\Form\JsonForms\Control\JsonFormsSubmitButton;
use Civi\Funding\Form\JsonSchema\JsonSchemaString;
use Civi\Funding\Form\SonstigeAktivitaet\JsonSchema\AVK1JsonSchema;
use Civi\Funding\Form\SonstigeAktivitaet\UISchema\AVK1UiSchema;

class AVK1Form extends FundingForm {

  /**
   * @param string $currency
   * @param array<string, mixed> $data
   * @param array<string, string> $submitActions Map of action names to button labels.
   * @param array<string, \Civi\Funding\Form\JsonSchema\JsonSchema> $extraProperties
   */
  public function __construct(string $currency, array $data, array $submitActions, array $extraProperties = []) {
    $extraProperties['action'] = new JsonSchemaString(['enum' => array_keys($submitActions)]);
    $submitButtons = [];
    foreach ($submitActions as $name => $label) {
      $submitButtons[] = new JsonFormsSubmitButton('#/properties/action', $label, $name);
    }
    parent::__construct(new AVK1JsonSchema($extraProperties), new AVK1UiSchema($currency, $submitButtons), $data);
  }

}
