<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\Generic\ClearingItem;

use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class GetFieldsAction extends DAOGetFieldsAction {

  public function __construct(string $entityName) {
    parent::__construct($entityName, 'getFields');
  }

  /**
   * @phpstan-return list<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge(parent::getRecords(), [
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer(
          'clearing_process_id.application_process_id.funding_case_id.funding_program_id.currency'
        ),
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
