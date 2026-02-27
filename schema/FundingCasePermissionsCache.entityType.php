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
  'name' => 'FundingCasePermissionsCache',
  'table' => 'civicrm_funding_case_permissions_cache',
  'class' => 'CRM_Funding_DAO_FundingCasePermissionsCache',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Case Permissions Cache'),
    'title_plural' => E::ts('Funding Case Permissions Caches'),
    'description' => E::ts('Cache for FundingCase permissions'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'UI_funding_case_id_contact_id_is_remote' => [
      'fields' => [
        'funding_case_id' => TRUE,
        'contact_id' => TRUE,
        'is_remote' => TRUE,
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
      'description' => E::ts('Unique FundingCasePermissionsCache ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
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
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('No FK to contact to work with 0 (contact ID on CLI), too'),
    ],
    'is_remote' => [
      'title' => E::ts('Is Remote'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Indicates whether the permissions are for internal or remote requests'),
    ],
    'permissions' => [
      'title' => E::ts('Permissions'),
      'sql_type' => 'text',
      'input_type' => 'Text',
      'description' => E::ts('Permissions as JSON array. If NULL they have to be determined.'),
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
  ],
];
