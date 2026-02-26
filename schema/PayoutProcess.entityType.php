<?php
/**
 * Copyright (C) 2026 SYSTOPIA GmbH
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

use CRM_Funding_ExtensionUtil as E;

return [
  'name' => 'FundingPayoutProcess',
  'table' => 'civicrm_funding_payout_process',
  'class' => 'CRM_Funding_DAO_PayoutProcess',
  'getInfo' => fn() => [
    'title' => E::ts('Payout Process'),
    'title_plural' => E::ts('Payout Processes'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique PayoutProcess ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'funding_case_id' => [
      'title' => E::ts('Funding Case ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingCase'),
      'entity_reference' => [
        'entity' => 'FundingCase',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'status' => [
      'title' => E::ts('Status'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getPayoutProcessStatus',
      ],
    ],
    'creation_date' => [
      'title' => E::ts('Creation Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'modification_date' => [
      'title' => E::ts('Modification Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'amount_total' => [
      'title' => E::ts('Amount Total'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
    ],
  ],
];
