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
            'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.status:label',
          ],
          'orderBy' => [
            'id' => 'ASC',
          ],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'FundingClearingProcess AS FundingApplicationProcess_FundingClearingProcess_application_process_id_01',
              'LEFT',
              [
                'id',
                '=',
                'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.application_process_id',
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
              'type' => 'field',
              'key' => 'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.status:label',
              'dataType' => 'String',
              'label' => E::ts('Clearing Status'),
              'sortable' => TRUE,
            ],
            [
              'text' => E::ts('Actions'),
              'style' => 'default',
              'size' => 'btn-xs',
              'icon' => 'fa-bars',
              'links' => [
                [
                  'path' => 'civicrm/a#/funding/application/[id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open application'),
                  'style' => 'default',
                  'condition' => [],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                ],
                [
                  'path' => 'civicrm/a#/funding/clearing/[FundingApplicationProcess_FundingClearingProcess_application_process_id_01.id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open clearing'),
                  'style' => 'default',
                  'condition' => [
                    'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.id',
                    'IS NOT EMPTY',
                  ],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
              'label' => '',
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
