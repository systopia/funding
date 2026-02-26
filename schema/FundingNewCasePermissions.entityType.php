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
  'name' => 'FundingNewCasePermissions',
  'table' => 'civicrm_funding_new_case_permissions',
  'class' => 'CRM_Funding_DAO_FundingNewCasePermissions',
  'getInfo' => fn() => [
    'title' => E::ts('Funding New Case Permissions'),
    'title_plural' => E::ts('Funding New Case Permissionses'),
    'description' => E::ts('Defines the initial permissions for new funding cases'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingNewCasePermissions ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'funding_program_id' => [
      'title' => E::ts('Funding Program ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingProgram'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_program',
        'key_column' => 'id',
        'label_column' => 'title',
        'prefetch' => 'false',
      ],
      'entity_reference' => [
        'entity' => 'FundingProgram',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'type' => [
      'title' => E::ts('Type'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'properties' => [
      'title' => E::ts('Properties'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => TRUE,
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'permissions' => [
      'title' => E::ts('Permissions'),
      'sql_type' => 'varchar(512)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Permissions as JSON array'),
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
  ],
];
