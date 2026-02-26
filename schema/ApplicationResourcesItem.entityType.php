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
  'name' => 'FundingApplicationResourcesItem',
  'table' => 'civicrm_funding_app_resources_item',
  'class' => 'CRM_Funding_DAO_ApplicationResourcesItem',
  'getInfo' => fn() => [
    'title' => E::ts('Application Resources Item'),
    'title_plural' => E::ts('Application Resources Items'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'index_identifier_application_process_id' => [
      'fields' => [
        'identifier' => TRUE,
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
      'description' => E::ts('Unique FundingApplicationResourcesItem ID'),
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
        'prefetch' => 'false',
      ],
      'entity_reference' => [
        'entity' => 'ApplicationProcess',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'identifier' => [
      'title' => E::ts('Identifier'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'type' => [
      'title' => E::ts('Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'amount' => [
      'title' => E::ts('Amount'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
      'required' => TRUE,
    ],
    'properties' => [
      'title' => E::ts('Properties'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'data_pointer' => [
      'title' => E::ts('Data Pointer'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('JSON pointer to data in application data'),
    ],
  ],
];
