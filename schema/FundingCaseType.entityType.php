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
  'name' => 'FundingCaseType',
  'table' => 'civicrm_funding_case_type',
  'class' => 'CRM_Funding_DAO_FundingCaseType',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Case Type'),
    'title_plural' => E::ts('Funding Case Types'),
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
      'description' => E::ts('Unique FundingCaseType ID'),
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
    'name' => [
      'title' => E::ts('Name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'is_combined_application' => [
      'title' => E::ts('Is Combined Application'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
    ],
    'application_process_label' => [
      'title' => E::ts('Application Process Label'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Used for combined applications'),
    ],
    'properties' => [
      'title' => E::ts('Properties'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
  ],
];
