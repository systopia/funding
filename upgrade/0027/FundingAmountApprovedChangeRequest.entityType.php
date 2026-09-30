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

use Civi\Funding\FundingPseudoConstants;

return [
  'name' => 'FundingAmountApprovedChangeRequest',
  'table' => 'civicrm_funding_amount_approved_change_request',
  'class' => 'CRM_Funding_DAO_FundingAmountApprovedChangeRequest',
  'getFields' => fn() => [
    'id' => [
      'title' => 'ID',
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => 'Unique FundingAmountApprovedChangeRequest ID',
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'funding_case_id' => [
      'title' => 'Funding Case ID',
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => 'FK to FundingCase',
      'entity_reference' => [
        'entity' => 'FundingCase',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'status' => [
      'title' => 'Status',
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'callback' => [FundingPseudoConstants::class, 'getFundingAmountApprovedChangeRequestStatus'],
      ],
    ],
    'creation_date' => [
      'title' => 'Creation Date',
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
    ],
    'creation_contact_id' => [
      'title' => 'Creation Contact ID',
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'amount_requested' => [
      'title' => 'Amount Requested',
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'data_type' => 'Money',
    ],
    'comment' => [
      'title' => 'Comment',
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => FALSE,
    ],
    'decision_date' => [
      'title' => 'Decision Date',
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => FALSE,
    ],
    'decision_contact_id' => [
      'title' => 'Decision Contact ID',
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => FALSE,
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'amount_approved' => [
      'title' => 'Amount Approved',
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'required' => FALSE,
      'data_type' => 'Money',
    ],
  ],
];
