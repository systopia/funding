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
  'name' => 'FundingFormStringTranslation',
  'table' => 'civicrm_funding_form_string_translation',
  'class' => 'CRM_Funding_DAO_FundingFormStringTranslation',
  'getInfo' => fn() => [
    'title' => E::ts('Funding Form String Translation'),
    'title_plural' => E::ts('Funding Form String Translations'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getPaths' => fn() => [
    'update' => 'civicrm/funding/funding/form-string-translation/edit#?FundingFormStringTranslation1=[id]',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingFormStringTranslation ID'),
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
        'prefetch' => 'false',
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
        'prefetch' => 'false',
      ],
      'entity_reference' => [
        'entity' => 'FundingCaseType',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'msg_text' => [
      'title' => E::ts('Original'),
      /**
       * We need a collation that is accent-sensitive and case-sensitive. However, the
       * chosen collation utf8mb4_bin doesn't sort in natural order. Collations
       * that are also sorting in natural order are named differently on MariaDB
       * and MySQL, though. (MariaDB: utf8mb4_0900_as_cs, MySQL:
       * utf8mb4_0900_as_cs) Since MariaDB 11.4.5 there's a mapping of MySQL
       * collations: https://jira.mariadb.org/browse/MDEV-20912.
       */
      'sql_type' => 'varchar(8000) COLLATE utf8mb4_bin',
      'input_type' => 'TextArea',
      'data_type' => 'String',
      'required' => TRUE,
      'description' => E::ts('Original'),
    ],
    /**
     * The field name is chosen to be skipped in \CRM_Utils_API_HTMLInputCoder,
     * see \CRM_Utils_API_HTMLInputCoder::getSkipFields().
     */
    'new_text' => [
      'title' => E::ts('Actual'),
      /**
       * Because this field is marked as not required CiviCRM adds NULL to the type
       * in the SQL code, resulting in "varchar(8000) NOT NULL". So NULL is not
       * allowed by the schema and at the same time the empty string may be used in
       * SearchKit's in-place edit.
       *
       * For the collation see comment on msg_text.
       */
      'sql_type' => 'varchar(8000) COLLATE utf8mb4_bin NOT',
      'input_type' => 'TextArea',
      'data_type' => 'String',
      'description' => E::ts('Actual string'),
    ],
    'modification_date' => [
      'title' => E::ts('Modification Date'),
      'sql_type' => 'timestamp ON UPDATE CURRENT_TIMESTAMP',
      'input_type' => 'Select Date',
      'data_type' => 'Timestamp',
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
  ],
];
