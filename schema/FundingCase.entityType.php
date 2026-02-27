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
  'name' => 'FundingCase',
  'table' => 'civicrm_funding_case',
  'class' => 'CRM_Funding_DAO_FundingCase',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Case'),
    'title_plural' => E::ts('Funding Cases'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_identifier' => [
      'fields' => [
        'identifier' => TRUE,
      ],
      'unique' => TRUE,
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingCase ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'identifier' => [
      'title' => E::ts('Identifier'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Unique generated identifier'),
    ],
    'funding_program_id' => [
      'title' => E::ts('Funding Program'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingProgram'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_program',
        'key_column' => 'id',
        'label_column' => 'title',
        'prefetch' => FALSE,
      ],
      'entity_reference' => [
        'entity' => 'FundingProgram',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'funding_case_type_id' => [
      'title' => E::ts('Funding Case Type ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingCaseType'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_case_type',
        'key_column' => 'id',
        'label_column' => 'title',
        'prefetch' => FALSE,
      ],
      'entity_reference' => [
        'entity' => 'FundingCaseType',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'status' => [
      'title' => E::ts('Status'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getFundingCaseStatus',
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
    'creation_contact_id' => [
      'title' => E::ts('Creation Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'notification_contact_ids' => [
      'title' => E::ts('Notification Contact Ids'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'recipient_contact_id' => [
      'title' => E::ts('Recipient'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'amount_approved' => [
      'title' => E::ts('Amount Approved'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
    ],
  ],
];
