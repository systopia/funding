<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_MyFundingTasks',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'MyFundingTasks',
        'label' => E::ts('My Funding Tasks'),
        'api_entity' => 'FundingTask',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'subject',
            'modified_date',
            'funding_case_task.affected_identifier',
            'source_record_id',
            'activity_type_id:label',
            'GROUP_CONCAT(DISTINCT FundingTask_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title) AS GROUP_CONCAT_FundingTask_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01_title',
            'GROUP_CONCAT(DISTINCT FundingTask_FundingCase_funding_case_id_01.recipient_contact_id.display_name) AS GROUP_CONCAT_FundingTask_FundingCase_funding_case_id_01_recipient_contact_id_display_name',
          ],
          'orderBy' => [],
          'where' => [
            [
              'OR',
              [
                [
                  'assignee_contact_id',
                  'IS EMPTY',
                ],
                [
                  'assignee_contact_id',
                  'CONTAINS',
                  'user_contact_id',
                ],
              ],
            ],
            [
              'status_type_id:name',
              '=',
              '0',
            ],
          ],
          'groupBy' => ['id'],
          'join' => [
            [
              'FundingCase AS FundingTask_FundingCase_funding_case_id_01',
              'INNER',
              [
                'funding_case_task.funding_case_id',
                '=',
                'FundingTask_FundingCase_funding_case_id_01.id',
              ],
            ],
            [
              'FundingProgram AS FundingTask_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01',
              'LEFT',
              [
                'FundingTask_FundingCase_funding_case_id_01.funding_program_id',
                '=',
                'FundingTask_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.id',
              ],
            ],
          ],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'SavedSearch_MyFundingTasks_SearchDisplay_table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'MyFundingTasks',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'subject',
              'dataType' => 'String',
              'label' => E::ts('Subject'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'modified_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Modified Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'funding_case_task.affected_identifier',
              'dataType' => 'String',
              'label' => E::ts('Affected Identifier'),
              'sortable' => FALSE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_CONCAT_FundingTask_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01_title',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_CONCAT_FundingTask_FundingCase_funding_case_id_01_recipient_contact_id_display_name',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'path' => 'civicrm/a#funding/case/[funding_case_task.funding_case_id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open case'),
                  'style' => 'default',
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'conditions' => [
                    [
                      'activity_type_id:name',
                      'IN',
                      [
                        'funding_case_task',
                        'funding_drawdown_task',
                      ],
                    ],
                  ],
                ],
                [
                  'path' => 'civicrm/funding/application/show?id=[source_record_id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open application'),
                  'style' => 'default',
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'conditions' => [
                    [
                      'activity_type_id:name',
                      '=',
                      'funding_application_process_task',
                    ],
                  ],
                ],
                [
                  'path' => 'civicrm/funding/clearing/show?applicationProcessId=[funding_application_process_task.application_process_id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open clearing'),
                  'style' => 'default',
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'conditions' => [
                    [
                      'activity_type_id:name',
                      '=',
                      'funding_clearing_process_task',
                    ],
                  ],
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => ['download'],
          'classes' => ['table', 'table-striped'],
          'actions_display_mode' => 'menu',
          'columnMode' => 'custom',
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
