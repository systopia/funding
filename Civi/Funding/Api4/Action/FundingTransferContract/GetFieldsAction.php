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

namespace Civi\Funding\Api4\Action\FundingTransferContract;

use Civi\Api4\Generic\BasicGetFieldsAction;
use Civi\Api4\FundingTransferContract;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends BasicGetFieldsAction {

  public function __construct() {
    parent::__construct(FundingTransferContract::_getEntityName(), 'getFields');
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   */
  public function fields(): array {
    return [
      [
        'name' => 'name',
        'data_type' => 'String',
        'description' => E::ts('Unique field identifier'),
      ],
      [
        'name' => 'title',
        'data_type' => 'String',
        'description' => E::ts('Technical name of field, shown in API and exports'),
      ],
      [
        'name' => 'type',
        'data_type' => 'String',
        'default_value' => 'Extra',
        'options' => [
          'Field' => E::ts('Primary Field'),
          'Custom' => E::ts('Custom Field'),
          'Filter' => E::ts('Search Filter'),
          'Extra' => E::ts('Extra API Field'),
        ],
      ],
      [
        'name' => 'nullable',
        'description' => 'Whether a null value is allowed in this field',
        'data_type' => 'Boolean',
        'default_value' => FALSE,
      ],
      [
        'name' => 'options',
        'data_type' => 'Array',
        'default_value' => FALSE,
      ],
      [
        'name' => 'operators',
        'data_type' => 'Array',
        'description' => 'If set, limits the operators that can be used on this field for "get" actions.',
        'default_value' => [],
      ],
      [
        'name' => 'data_type',
        'options' => [
          'Array' => E::ts('Array'),
          'Boolean' => E::ts('Boolean'),
          'Date' => E::ts('Date'),
          'Float' => E::ts('Float'),
          'Integer' => E::ts('Integer'),
          'String' => E::ts('String'),
          'Text' => E::ts('Text'),
          'Timestamp' => E::ts('Timestamp'),
        ],
      ],
      [
        'name' => 'serialize',
        'data_type' => 'Integer',
        'default_value' => 0,
      ],
      [
        'name' => 'readonly',
        'data_type' => 'Boolean',
        'default_value' => TRUE,
      ],
    ];
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   */
  public function getRecords(): array {
    return [
      [
        'name' => 'funding_case_id',
        'title' => 'funding_case_id',
        'data_type' => 'Integer',
        'operators' => ['='],
      ],
      [
        'name' => 'title',
        'title' => E::ts('Title'),
        'data_type' => 'String',
      ],
      [
        'name' => 'amount_approved',
        'title' => E::ts('Amount Approved'),
        'data_type' => 'Float',
      ],
      [
        'name' => 'payout_process_id',
        'title' => 'payout_process_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'amount_paid_out',
        'title' => E::ts('Amount Paid Out'),
        'data_type' => 'Float',
      ],
      [
        'name' => 'amount_available',
        'title' => E::ts('Amount Available'),
        'data_type' => 'Float',
      ],
      [
        'name' => 'uri',
        'title' => E::ts('URI to Transfer Contract'),
        'data_type' => 'String',
      ],
      [
        'name' => 'funding_case_type_id',
        'title' => 'funding_case_type_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'funding_program_id',
        'title' => 'funding_program_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'data_type' => 'String',
      ],
      [
        'name' => 'funding_program_title',
        'title' => E::ts('Funding Program Title'),
        'data_type' => 'String',
      ],
      [
        'name' => 'CAN_create_drawdown',
        'title' => 'CAN_create_drawdown',
        'data_type' => 'Boolean',
      ],
    ];
  }

}
