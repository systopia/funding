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
  'name' => 'FundingApplicationSnapshot',
  'table' => 'civicrm_funding_application_snapshot',
  'class' => 'CRM_Funding_DAO_ApplicationSnapshot',
  'getInfo' => fn() => [
    'title' => E::ts('Application Snapshot'),
    'title_plural' => E::ts('Application Snapshots'),
    'description' => E::ts('Snapshots of application versions that need to be preserved'),
    'log' => TRUE,
    'label_field' => 'title',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingApplicationSnapshot ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'application_process_id' => [
      'title' => E::ts('Application Process ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingApplicationProcess'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_application_process',
        'key_column' => 'id',
        'label_column' => 'title',
        'prefetch' => FALSE,
      ],
      'entity_reference' => [
        'entity' => 'FundingApplicationProcess',
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
    'title' => [
      'title' => E::ts('Title'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'short_description' => [
      'title' => E::ts('Short Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'start_date' => [
      'title' => E::ts('Start Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'end_date' => [
      'title' => E::ts('End Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
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
    'cost_items' => [
      'title' => E::ts('Cost Items'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'resources_items' => [
      'title' => E::ts('Resources Items'),
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
    'is_review_calculative' => [
      'title' => E::ts('Is Review Calculative'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
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
    'custom_fields' => [
      'title' => E::ts('Custom Fields'),
      'sql_type' => 'mediumtext',
      'input_type' => 'TextArea',
      'data_type' => 'Text',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
  ],
];
