<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_cases',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_cases',
        'label' => E::ts('Funding Cases'),
        'api_entity' => 'FundingCase',
        'api_params' => [
          'version' => 4,
          'select' => [
            'identifier',
            'status:label',
            'FundingCase_FundingProgram_funding_program_id_01.title',
            'FundingCase_FundingCaseType_funding_case_type_id_01.title',
            'recipient_contact_id.display_name',
            'amount_approved',
            'amount_paid_out',
            'withdrawable_funds',
            'amount_admitted',
            'amount_cleared',
            'creation_date',
            'modification_date',
            'application_process_review_progress',
          ],
          'orderBy' => [
            'id' => 'DESC',
          ],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'FundingProgram AS FundingCase_FundingProgram_funding_program_id_01',
              'INNER',
              [
                'funding_program_id',
                '=',
                'FundingCase_FundingProgram_funding_program_id_01.id',
              ],
            ],
            [
              'FundingCaseType AS FundingCase_FundingCaseType_funding_case_type_id_01',
              'INNER',
              [
                'funding_case_type_id',
                '=',
                'FundingCase_FundingCaseType_funding_case_type_id_01.id',
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
    'name' => 'SavedSearch_funding_cases_SearchDisplay_table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_cases',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 60,
          'pager' => [
            'show_count' => FALSE,
            'expose_limit' => TRUE,
          ],
          'placeholder' => 5,
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
              'key' => 'FundingCase_FundingProgram_funding_program_id_01.title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'FundingCase_FundingCaseType_funding_case_type_id_01.title',
              'dataType' => 'String',
              'label' => E::ts('Funding Case Type'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'recipient_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'application_process_review_progress',
              'dataType' => 'Integer',
              'label' => E::ts('Review Progress'),
              'sortable' => TRUE,
              'rewrite' => '[application_process_review_progress] %',
            ],
            [
              'type' => 'field',
              'key' => 'amount_approved',
              'dataType' => 'Money',
              'label' => E::ts('Amount Approved'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'withdrawable_funds',
              'dataType' => 'String',
              'label' => E::ts('Withdrawable Funds'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_paid_out',
              'dataType' => 'Money',
              'label' => E::ts('Amount Paid Out'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'amount_admitted',
              'dataType' => 'Money',
              'label' => E::ts('Amount Admitted'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'amount_cleared',
              'dataType' => 'Money',
              'label' => E::ts('Amount Cleared'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'creation_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Creation Date'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'modification_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Modification Date'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'text' => E::ts('Actions'),
              'style' => 'default',
              'size' => 'btn-sm',
              'icon' => 'fa-bars',
              'links' => [
                [
                  'path' => 'civicrm/a#funding/case/[id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open case'),
                  'style' => 'default',
                  'condition' => [],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/a/#/funding/case/[id]/permissions',
                  'icon' => 'fa-pencil-square-o',
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
                  'target' => '',
                  'task' => '',
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => [
            'download',
          ],
          'classes' => [
            'table',
            'table-striped',
          ],
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
