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
    'name' => 'SavedSearch_FundingApplicationProcesses',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_processes',
        'label' => E::ts('Funding Applications'),
        'api_entity' => 'FundingApplicationProcess',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'identifier',
            'title',
            'short_description',
            'amount_requested',
            'status:label',
            'is_eligible',
            'funding_case_id',
            'is_review_calculative',
            'is_review_content',
          ],
          'orderBy' => [
            'id' => 'ASC',
          ],
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
    'name' => 'SearchDisplay_FundingApplicationProcesses.Table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_application_processes',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 60,
          'pager' => [
            'show_count' => FALSE,
            'expose_limit' => TRUE,
          ],
          'placeholder' => 1,
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
              'key' => 'short_description',
              'dataType' => 'String',
              'label' => E::ts('Short Description'),
              'sortable' => FALSE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_requested',
              'dataType' => 'Money',
              'label' => E::ts('Amount Requested'),
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
              'size' => 'btn-xs',
              'links' => [
                [
                  'path' => 'civicrm/a#/funding/application/[id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open application'),
                  'style' => 'default',
                  'condition' => [],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'task' => '',
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => [
            'civiofficeRender',
          ],
          'classes' => [
            'table',
            'table-striped',
          ],
          'headerCount' => FALSE,
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
