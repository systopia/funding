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
    'name' => 'SavedSearch_FundingPrograms',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_programs',
        'label' => E::ts('Funding Programs'),
        'form_values' => NULL,
        'mapping_id' => NULL,
        'search_custom_id' => NULL,
        'api_entity' => 'FundingProgram',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'title',
            'abbreviation',
            'identifier_prefix',
            'start_date',
            'end_date',
            'requests_start_date',
            'requests_end_date',
            'currency:label',
            'budget',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
        'expires_date' => NULL,
        'description' => NULL,
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_FundingPrograms.Table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_programs',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 10,
          'pager' => [
            'show_count' => FALSE,
            'expose_limit' => TRUE,
          ],
          'placeholder' => 5,
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
              'key' => 'identifier_prefix',
              'dataType' => 'String',
              'label' => E::ts('Identifier Prefix'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'start_date',
              'dataType' => 'Date',
              'label' => E::ts('Start Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'end_date',
              'dataType' => 'Date',
              'label' => E::ts('End Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'requests_start_date',
              'dataType' => 'Date',
              'label' => E::ts('Requests Start Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'requests_end_date',
              'dataType' => 'Date',
              'label' => E::ts('Requests End Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'currency:label',
              'dataType' => 'String',
              'label' => E::ts('Currency'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'budget',
              'dataType' => 'Money',
              'label' => E::ts('Budget'),
              'sortable' => TRUE,
            ],
            [
              'text' => E::ts('Actions'),
              'style' => 'default',
              'size' => 'btn-sm',
              'icon' => 'fa-bars',
              'links' => [
                [
                  'path' => 'civicrm/a/#/funding/program/[id]/recipients',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Edit recipients'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    'administer Funding',
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/a/#/funding/program/[id]/permissions',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Edit permissions'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    'administer Funding',
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/a/#/funding/program/[id]/new-case-permissions',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Edit initial funding case permissions'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    'administer Funding',
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/funding/program/edit#?FundingProgram1=[id]',
                  'icon' => 'fa-pencil',
                  'text' => E::ts('Edit basic values'),
                  'style' => 'default',
                  'condition' => [
                    'check user permission',
                    '=',
                    'administer Funding',
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'task' => '',
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => TRUE,
          'classes' => [
            'table',
            'table-striped',
          ],
        ],
        'acl_bypass' => FALSE,
      ],
    ],
  ],
];
