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
  'name' => 'FundingApplicationProcess',
  'table' => 'civicrm_funding_application_process',
  'class' => 'CRM_Funding_DAO_ApplicationProcess',
  'getInfo' => fn() => [
    'title' => E::ts('Application Process'),
    'title_plural' => E::ts('Application Processes'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
    'label_field' => 'title',
  ],
  'getIndices' => fn() => [
    'index_identifier' => [
      'fields' => [
        'identifier' => TRUE,
      ],
      'unique' => TRUE,
    ],
    'index_is_eligible' => [
      'fields' => [
        'is_eligible' => TRUE,
      ],
    ],
    'index_is_in_work' => [
      'fields' => [
        'is_in_work' => TRUE,
      ],
    ],
    'index_is_rejected' => [
      'fields' => [
        'is_rejected' => TRUE,
      ],
    ],
    'index_is_withdrawn' => [
      'fields' => [
        'is_withdrawn' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingApplicationProcess ID'),
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
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getApplicationProcessStatus',
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
    'title' => [
      'title' => E::ts('Title'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'short_description' => [
      'title' => E::ts('Short Description'),
      'sql_type' => 'varchar(500)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'start_date' => [
      'title' => E::ts('Start Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('Scheduled start of the activity'),
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'end_date' => [
      'title' => E::ts('End Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('Scheduled end of the activity'),
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'request_data' => [
      'title' => E::ts('Request Data'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'amount_requested' => [
      'title' => E::ts('Amount Requested'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
      'required' => TRUE,
    ],
    'is_review_content' => [
      'title' => E::ts('Is Review Content'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
    ],
    /**
     * shortened because of limited FK identifier length
     */
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
    /**
     * shortened because of limited FK identifier length
     */
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
    'is_eligible' => [
      'title' => E::ts('Is Eligible'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
    ],
    'is_in_work' => [
      'title' => E::ts('Is In Work'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Is the application in work by the applicant?'),
    ],
    'is_rejected' => [
      'title' => E::ts('Is Rejected'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
    ],
    'is_withdrawn' => [
      'title' => E::ts('Is Withdrawn'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
    ],
  ],
];
