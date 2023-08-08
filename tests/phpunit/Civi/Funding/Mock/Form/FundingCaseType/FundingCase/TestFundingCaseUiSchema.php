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

namespace Civi\Funding\Mock\Form\FundingCaseType\FundingCase;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class TestFundingCaseUiSchema extends JsonFormsGroup {

  /**
   * @phpstan-param array<int, \Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton> $submitButtons
   * @phpstan-param array<int, \Civi\RemoteTools\JsonForms\Control\JsonFormsHidden> $hiddenFields
   */
  public function __construct(array $submitButtons = [], array $hiddenFields = []) {
    parent::__construct('Test', [
      new JsonFormsControl('#/properties/title', 'Title'),
      ...$submitButtons,
      ...$hiddenFields,
    ]);
  }

}
