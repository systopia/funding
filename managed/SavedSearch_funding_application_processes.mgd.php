<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_application_processes',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
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
    'name' => 'SavedSearch_funding_application_processes_SearchDisplay_table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
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
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'title',
              'dataType' => 'String',
              'label' => E::ts('Title'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'short_description',
              'dataType' => 'String',
              'label' => E::ts('Short Description'),
              'sortable' => FALSE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'amount_requested',
              'dataType' => 'Money',
              'label' => E::ts('Amount Requested'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'status:label',
              'dataType' => 'String',
              'label' => E::ts('Status'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'is_review_content',
              'dataType' => 'Boolean',
              'label' => E::ts('Content Review'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'is_review_calculative',
              'dataType' => 'Boolean',
              'label' => E::ts('Calculative Review'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.status:label',
              'dataType' => 'String',
              'label' => E::ts('Clearing Status'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'path' => 'civicrm/a#/funding/application/[id]',
                  'icon' => 'fa-folder-open',
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
                  'icon' => 'fa-folder-open',
                  'text' => E::ts('Open clearing'),
                  'style' => 'default',
                  'condition' => [
                    'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.status:name',
                    '!=',
                    'not-started',
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
              'label' => E::ts('Actions'),
            ],
          ],
          'actions' => [
            'civiofficeRender',
            'download',
          ],
          'classes' => [
            'table',
            'table-striped',
          ],
          'headerCount' => TRUE,
          'actions_display_mode' => 'menu',
          'tally' => [
            'label' => E::ts('Total'),
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
