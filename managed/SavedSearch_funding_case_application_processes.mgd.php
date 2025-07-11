<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_case_application_processes',
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
            'start_date',
            'status:label',
            'is_review_calculative',
            'is_review_content',
            'creation_date',
            'modification_date',
            'funding_case_id',
            'FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title',
            'FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
            'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.id',
            'FundingApplicationProcess_FundingClearingProcess_application_process_id_01.status:label',
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
    'name' => 'SavedSearch_funding_case_application_processes_SearchDisplay_table',
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
              'key' => 'start_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Start Date'),
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
                [
                  'path' => 'civicrm/a/#/funding/clearing/[FundingApplicationProcess_FundingClearingProcess_application_process_id_01.id]',
                  'icon' => 'fa-external-link',
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
                [
                  'path' => 'civicrm/a/#/funding/case/[funding_case_id]/permissions',
                  'icon' => 'fa-pencil-square-o',
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
                  'target' => '',
                  'task' => '',
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => [
            'civiofficeRender',
            'download',
          ],
          'actions_display_mode' => 'menu',
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
