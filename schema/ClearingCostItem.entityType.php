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
  'name' => 'FundingClearingCostItem',
  'table' => 'civicrm_funding_clearing_cost_item',
  'class' => 'CRM_Funding_DAO_ClearingCostItem',
  'getInfo' => fn() => [
    'title' => E::ts('Clearing Cost Item'),
    'title_plural' => E::ts('Clearing Cost Items'),
    'description' => E::ts('Clearing for an application cost item'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingClearingCostItem ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'clearing_process_id' => [
      'title' => E::ts('Clearing Process ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingClearingProcess'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_clearing_process',
        'key_column' => 'id',
        'label_column' => 'id',
        'prefetch' => 'false',
      ],
      'entity_reference' => [
        'entity' => 'ClearingProcess',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'application_cost_item_id' => [
      'title' => E::ts('Application Cost Item ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingApplicationResourcesItem'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_app_cost_item',
        'key_column' => 'id',
        'label_column' => 'identifier',
        'prefetch' => 'false',
      ],
      'entity_reference' => [
        'entity' => 'ApplicationCostItem',
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
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getClearingItemStatus',
      ],
    ],
    'file_id' => [
      'title' => E::ts('File ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to File'),
      'entity_reference' => [
        'entity' => 'File',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'receipt_number' => [
      'title' => E::ts('Receipt Number'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'receipt_date' => [
      'title' => E::ts('Receipt Date'),
      'sql_type' => 'date',
      'input_type' => 'Select Date',
      'input_attrs' => [
        'format_type' => 'activityDate',
      ],
    ],
    'payment_date' => [
      'title' => E::ts('Payment Date'),
      'sql_type' => 'date',
      'input_type' => 'Select Date',
      'input_attrs' => [
        'format_type' => 'activityDate',
      ],
    ],
    'payment_party' => [
      'title' => E::ts('Payment Party'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'reason' => [
      'title' => E::ts('Reason'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'amount' => [
      'title' => E::ts('Amount'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
      'required' => TRUE,
    ],
    'amount_admitted' => [
      'title' => E::ts('Amount Admitted'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
    ],
    'properties' => [
      'title' => E::ts('Properties'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'form_key' => [
      'title' => E::ts('Form Key'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
  ],
];
