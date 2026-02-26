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
  'name' => 'FundingProgram',
  'table' => 'civicrm_funding_program',
  'class' => 'CRM_Funding_DAO_FundingProgram',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Program'),
    'title_plural' => E::ts('Funding Programs'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
    'label_field' => 'title',
  ],
  'getIndices' => fn() => [
    'index_title' => [
      'fields' => [
        'title' => E::ts(TRUE),
      ],
      'unique' => TRUE,
    ],
    'index_abbreviation' => [
      'fields' => [
        'abbreviation' => TRUE,
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
      'description' => E::ts('Unique FundingProgram ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'title' => [
      'title' => E::ts('Title'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'abbreviation' => [
      'title' => E::ts('Abbreviation'),
      'sql_type' => 'varchar(20)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'identifier_prefix' => [
      'title' => E::ts('Identifier Prefix'),
      'sql_type' => 'varchar(100)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('The database ID of a funding case will be appended to this prefix and forms its identifier'),
    ],
    'start_date' => [
      'title' => E::ts('Start Date'),
      'sql_type' => 'date',
      'input_type' => 'Select Date',
      'required' => TRUE,
    ],
    'end_date' => [
      'title' => E::ts('End Date'),
      'sql_type' => 'date',
      'input_type' => 'Select Date',
      'required' => TRUE,
    ],
    'requests_start_date' => [
      'title' => E::ts('Requests Start Date'),
      'sql_type' => 'date',
      'input_type' => 'Select Date',
      'required' => TRUE,
    ],
    'requests_end_date' => [
      'title' => E::ts('Requests End Date'),
      'sql_type' => 'date',
      'input_type' => 'Select Date',
      'required' => TRUE,
    ],
    'currency' => [
      'title' => E::ts('Currency'),
      'sql_type' => 'varchar(10)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'table' => 'civicrm_currency',
        'key_column' => 'name',
        'label_column' => 'full_name',
        'name_column' => 'name',
        'abbr_column' => 'symbol',
      ],
    ],
    'budget' => [
      'title' => E::ts('Budget'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Number',
      'data_type' => 'Money',
      // step 0.01
    ],
  ],
];
