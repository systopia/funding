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

use CRM_Funding_ExtensionUtil as E;

return [
  'name' => 'FundingAmountApprovedChangeRequest',
  'table' => 'civicrm_funding_amount_approved_change_request',
  'class' => 'CRM_Funding_DAO_FundingAmountApprovedChangeRequest',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Amount Approved Change Request'),
    'title_plural' => E::ts('Funding Amount Approved Change Requests'),
    'description' => '',
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingAmountApprovedChangeRequest ID'),
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
        'callback' => [FundingPseudoConstants::class, 'getFundingAmountApprovedChangeRequestStatus'],
      ],
    ],
    'creation_date' => [
      'title' => E::ts('Creation Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
    ],
    'creation_contact_id' => [
      'title' => E::ts('Creation Contact ID'),
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
      'title' => E::ts('Amount Requested'),
      'sql_type' => 'decimal(15,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'data_type' => 'Money',
    ],
    'comment' => [
      'title' => E::ts('Comment'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => FALSE,
    ],
    'decision_date' => [
      'title' => E::ts('Decision Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => FALSE,
    ],
    'decision_contact_id' => [
      'title' => E::ts('Decision Contact ID'),
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
      'title' => E::ts('Amount Approved'),
      'sql_type' => 'decimal(15,2)',
      'input_type' => 'Text',
      'required' => FALSE,
      'data_type' => 'Money',
    ],
  ],
];
