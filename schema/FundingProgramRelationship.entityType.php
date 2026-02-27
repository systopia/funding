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
  'name' => 'FundingProgramRelationship',
  'table' => 'civicrm_funding_program_relationship',
  'class' => 'CRM_Funding_DAO_FundingProgramRelationship',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Program Relationship'),
    'title_plural' => E::ts('Funding Program Relationships'),
    'description' => E::ts('Stores relationships between FundingProgram entities'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingProgramRelationship ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'id_a' => [
      'title' => E::ts('ID A'),
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
    'id_b' => [
      'title' => E::ts('ID B'),
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
    'type' => [
      'title' => E::ts('Type'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getFundingProgramRelationshipTypes',
      ],
    ],
  ],
];
