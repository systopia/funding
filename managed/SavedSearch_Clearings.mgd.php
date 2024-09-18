<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

// phpcs:disable Generic.Files.LineLength.TooLong
return [
  [
    'name' => 'SavedSearch_Clearings',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Clearings',
        'label' => E::ts('Clearings'),
        'api_entity' => 'FundingClearingProcess',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'FundingClearingProcess_FundingApplicationProcess_application_process_id_01.identifier',
            'application_process_id.title',
            'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title',
            'status:label',
            'is_review_calculative',
            'is_review_content',
            'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01.title',
            'amount_cleared',
            'amount_admitted',
            'creation_date',
            'modification_date',
            'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'FundingApplicationProcess AS FundingClearingProcess_FundingApplicationProcess_application_process_id_01',
              'INNER',
              [
                'application_process_id',
                '=',
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01.id',
              ],
            ],
            [
              'FundingCase AS FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01',
              'INNER',
              [
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01.funding_case_id',
                '=',
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01.id',
              ],
            ],
            [
              'FundingProgram AS FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01',
              'INNER',
              [
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01.funding_program_id',
                '=',
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.id',
              ],
            ],
            [
              'FundingCaseType AS FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01',
              'INNER',
              [
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01.funding_case_type_id',
                '=',
                'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01.id',
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
    'name' => 'SavedSearch_Clearings_SearchDisplay_Table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'Clearings',
        'type' => 'table',
        'settings' => [
          'description' => '',
          'sort' => [],
          'limit' => 50,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'FundingClearingProcess_FundingApplicationProcess_application_process_id_01.identifier',
              'dataType' => 'String',
              'label' => E::ts('Identifier'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'application_process_id.title',
              'dataType' => 'String',
              'label' => E::ts('Title'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
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
              'key' => 'is_review_calculative',
              'dataType' => 'Boolean',
              'label' => E::ts('Review Calculative'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'is_review_content',
              'dataType' => 'Boolean',
              'label' => E::ts('Review Content'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01_FundingCase_FundingCaseType_funding_case_type_id_01.title',
              'dataType' => 'String',
              'label' => E::ts('Funding Case Type'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
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
              'type' => 'field',
              'key' => 'FundingClearingProcess_FundingApplicationProcess_application_process_id_01_FundingApplicationProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'text' => '',
              'style' => 'default',
              'size' => 'btn-sm',
              'icon' => 'fa-bars',
              'links' => [
                [
                  'path' => 'civicrm/a/#/funding/clearing/[id]',
                  'icon' => 'fa-folder-open-o',
                  'text' => E::ts('Open clearing'),
                  'style' => 'default',
                  'condition' => [],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                ],
                [
                  'path' => 'civicrm/a/#/funding/application/[application_process_id]',
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
                  'path' => 'civicrm/a/#/funding/case/[FundingClearingProcess_FundingApplicationProcess_application_process_id_01.funding_case_id]/permissions',
                  'icon' => 'fa-pencil-square-o',
                  'text' => E::ts('Edit permissions'),
                  'style' => 'default',
                  'condition' => [],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                ],
              ],
              'type' => 'menu',
              'alignment' => 'text-right',
              'label' => E::ts('Actions'),
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
