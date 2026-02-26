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
  'name' => 'FundingCaseContactRelation',
  'table' => 'civicrm_funding_case_contact_relation',
  'class' => 'CRM_Funding_DAO_FundingCaseContactRelation',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Case Contact Relation'),
    'title_plural' => E::ts('Funding Case Contact Relations'),
    'description' => E::ts('Stores which permissions a contact or a related contact has on a funding case'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingCaseContactRelation ID'),
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
