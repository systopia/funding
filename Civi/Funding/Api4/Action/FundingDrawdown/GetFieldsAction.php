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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\DAOGetFieldsAction;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class GetFieldsAction extends DAOGetFieldsAction {

  public function __construct() {
    parent::__construct(FundingDrawdown::getEntityName(), 'getFields');
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge(parent::getRecords(), [
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'type' => 'Custom',
        'data_type' => 'String',
        'readonly' => TRUE,
      ],
      [
        'name' => 'CAN_review',
        'type' => 'Custom',
        'data_type' => 'Boolean',
        'readonly' => TRUE,
        'required' => FALSE,
      ],
    ]);
  }

}
