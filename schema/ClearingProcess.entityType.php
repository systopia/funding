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
  'name' => 'FundingClearingProcess',
  'table' => 'civicrm_funding_clearing_process',
  'class' => 'CRM_Funding_DAO_ClearingProcess',
  'getInfo' => fn() => [
    'title' => E::ts('Clearing Process'),
    'title_plural' => E::ts('Clearing Processes'),
    'description' => E::ts(''),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_application_process_id' => [
      'fields' => [
        'application_process_id' => TRUE,
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
      'description' => E::ts('Unique FundingClearingProcess ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'application_process_id' => [
      'title' => E::ts('Application Process ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingApplicationProcess'),
      'entity_reference' => [
        'entity' => 'ApplicationProcess',
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
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getClearingProcessStatus',
      ],
    ],
    'creation_date' => [
      'title' => E::ts('Creation Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('Start of the clearing. (Not date of entity creation.)'),
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'modification_date' => [
      'title' => E::ts('Modification Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'start_date' => [
      'title' => E::ts('Start Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('Actual start of the activity'),
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'end_date' => [
      'title' => E::ts('End Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('Actual end of the activity'),
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'report_data' => [
      'title' => E::ts('Report Data'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'is_review_content' => [
      'title' => E::ts('Is Review Content'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
    ],
    'reviewer_cont_contact_id' => [
      'title' => E::ts('Reviewer Cont Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'is_review_calculative' => [
      'title' => E::ts('Is Review Calculative'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
    ],
    'reviewer_calc_contact_id' => [
      'title' => E::ts('Reviewer Calc Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
  ],
];
