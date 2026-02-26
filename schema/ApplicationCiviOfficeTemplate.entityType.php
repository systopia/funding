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
  'name' => 'FundingApplicationCiviOfficeTemplate',
  'table' => 'civicrm_funding_application_civioffice_template',
  'class' => 'CRM_Funding_DAO_ApplicationCiviOfficeTemplate',
  'getInfo' => fn() => [
    'title' => E::ts('Application Template'),
    'title_plural' => E::ts('Application Templates'),
    'description' => E::ts('Templates for use in application portal'),
    'log' => TRUE,
    'label_field' => 'label',
  ],
  'getIndices' => fn() => [
    'UI_case_type_id_label' => [
      'fields' => [
        'case_type_id' => TRUE,
        'label' => E::ts(TRUE),
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
      'description' => E::ts('Unique FundingApplicationCiviOfficeTemplate ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'case_type_id' => [
      'title' => E::ts('Case Type ID'),
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
    'document_uri' => [
      'title' => E::ts('Document'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Select',
      'required' => TRUE,
      'description' => E::ts('CiviOffice document URI'),
      'pseudoconstant' => [
        'callback' => 'Civi\\Funding\\DocumentRender\\CiviOffice\\CiviOfficePseudoConstants::getSharedDocumentUris',
      ],
    ],
    'label' => [
      'title' => E::ts('Label'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
  ],
];
