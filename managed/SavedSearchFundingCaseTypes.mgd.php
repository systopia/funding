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

return [
  [
    'name' => 'SavedSearch_funding_case_types',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_case_types',
        'label' => E::ts('Funding Case Types'),
        'api_entity' => 'FundingCaseType',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'title',
            'abbreviation',
            'is_combined_application',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_funding_case_types_SearchDisplay_table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_case_types',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 10,
          'pager' => [
            'expose_limit' => TRUE,
          ],
          'placeholder' => 3,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'title',
              'dataType' => 'String',
              'label' => E::ts('Title'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'abbreviation',
              'dataType' => 'String',
              'label' => E::ts('Abbreviation'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'is_combined_application',
              'dataType' => 'Boolean',
              'label' => E::ts('Is Combined Application'),
              'sortable' => TRUE,
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'path' => 'civicrm/funding/case-type/templates#?case_type_id=[id]',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Manage templates'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    [
                      'administer Funding',
                    ],
                  ],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                ],
                [
                  'path' => 'civicrm/funding/application-templates#?case_type_id=[id]',
                  'icon' => 'fa-pencil-square-o',
                  'text' => E::ts('Manage external application templates'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    [
                      'administer Funding',
                    ],
                  ],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'table',
            'table-striped',
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
