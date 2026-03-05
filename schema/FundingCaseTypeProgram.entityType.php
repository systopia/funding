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
  'name' => 'FundingCaseTypeProgram',
  'table' => 'civicrm_funding_case_type_program',
  'class' => 'CRM_Funding_DAO_FundingCaseTypeProgram',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Case Type Program'),
    'title_plural' => E::ts('Funding Case Type Programs'),
    'description' => E::ts('Stores which funding case types are available in a funding program'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_funding_case_type_program' => [
      'fields' => [
        'funding_program_id' => TRUE,
        'funding_case_type_id' => TRUE,
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
      'description' => E::ts('Unique FundingCaseTypeProgram ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
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
        'on_delete' => 'CASCADE',
      ],
    ],
    'funding_case_type_id' => [
      'title' => E::ts('Funding Case Type'),
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
  ],
];
