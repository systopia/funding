<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

// phpcs:disable Generic.Files.LineLength.TooLong
return [
  [
    'name' => 'SavedSearch_FundingCaseApplicationProcesses',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_case_application_processes',
        'label' => E::ts('Funding Case Applications'),
        'api_entity' => 'FundingApplicationProcess',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'identifier',
            'title',
            'status:label',
            'is_review_calculative',
            'is_review_content',
            'creation_date',
            'modification_date',
            'funding_case_id',
            'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title',
            'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01.title',
            'FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'FundingCase AS FundingApplicationProcess_FundingCase_funding_case_id_01',
              'INNER',
              [
                'funding_case_id',
                '=',
                'FundingApplicationProcess_FundingCase_funding_case_id_01.id',
              ],
            ],
            [
              'FundingProgram AS FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01',
              'INNER',
              [
                'FundingApplicationProcess_FundingCase_funding_case_id_01.funding_program_id',
                '=',
                'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.id',
              ],
            ],
            [
              'FundingCaseType AS FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01',
              'INNER',
              [
                'FundingApplicationProcess_FundingCase_funding_case_id_01.funding_case_type_id',
                '=',
                'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01.id',
              ],
            ],
          ],
          'having' => [],
        ],
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'SearchDisplay_FundingCaseApplicationProcesses.Table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_case_application_processes',
        'type' => 'table',
        'settings' => [
          'limit' => 60,
          'classes' => [
            'table',
            'table-striped',
          ],
          'pager' => [
            'show_count' => FALSE,
            'expose_limit' => TRUE,
          ],
          'placeholder' => 5,
          'sort' => [
            [
              'id',
              'DESC',
            ],
          ],
          'columns' => [
            [
              'type' => 'field',
              'key' => 'identifier',
              'dataType' => 'String',
              'label' => E::ts('Identifier'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'title',
              'dataType' => 'String',
              'label' => E::ts('Title'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'status:label',
              'dataType' => 'String',
              'label' => E::ts('Status'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'is_review_content',
              'dataType' => 'Boolean',
              'label' => E::ts('Content Review'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'is_review_calculative',
              'dataType' => 'Boolean',
              'label' => E::ts('Calculative Review'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01.title',
              'dataType' => 'String',
              'label' => E::ts('Funding Case Type'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'creation_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Creation Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'modification_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Modification Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
            ],
            [
              'text' => E::ts('Actions'),
              'style' => 'default',
              'size' => 'btn-sm',
              'icon' => 'fa-bars',
              'links' => [
                [
                  'path' => 'civicrm/a/#/funding/application/[id]',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Open application'),
                  'style' => 'default',
                  'condition' => [],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/a/#/funding/case/[funding_case_id]/permissions',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Edit permissions'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    [
                      'administer Funding',
                    ],
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                  'task' => '',
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => [
            'civiofficeRender',
          ],
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
