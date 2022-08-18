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

use Civi\RemoteTools\Form\JsonSchema\JsonSchemaInteger;

final class AVK1FormNew extends AVK1Form {

  /**
   * @param string $currency
   * @param int $fundingCaseTypeId
   * @param int $fundingProgramId
   * @param array<int, string> $permissions
   * @param array<string, mixed> $data
   */
  public function __construct(string $currency, int $fundingCaseTypeId, int $fundingProgramId,
    array $permissions, array $data = []
  ) {
    $data['fundingCaseTypeId'] = $fundingCaseTypeId;
    $data['fundingProgramId'] = $fundingProgramId;

    $extraProperties = [
      'fundingCaseTypeId' => new JsonSchemaInteger(['const' => $fundingCaseTypeId, 'readonly' => TRUE]),
      'fundingProgramId' => new JsonSchemaInteger(['const' => $fundingProgramId, 'readonly' => TRUE]),
    ];

    $submitActions = iterator_to_array($this->getSubmitActions($permissions));

    parent::__construct($currency, $data, $submitActions, $extraProperties);
  }

  /**
   * @param array<int, string> $permissions
   *
   * @return iterable<string, string>
   */
  private function getSubmitActions(array $permissions): iterable {
    if (in_array('create_application', $permissions, TRUE)) {
      yield 'save' => 'Save';
    }
    if (in_array('apply_application', $permissions, TRUE)) {
      yield 'apply' => 'Apply';
    }
  }

}
