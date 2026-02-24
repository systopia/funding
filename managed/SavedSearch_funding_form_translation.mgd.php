<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_form_translation',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_form_translation',
        'label' => E::ts('Form Translation'),
        'api_entity' => 'FundingCaseTypeProgram',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'GROUP_FIRST(FundingCaseTypeProgram_FundingProgram_funding_program_id_01.title) AS GROUP_FIRST_FundingCaseTypeProgram_FundingProgram_funding_program_id_01_title',
            'GROUP_FIRST(FundingCaseTypeProgram_FundingCaseType_funding_case_type_id_01.title) AS GROUP_FIRST_FundingCaseTypeProgram_FundingCaseType_funding_case_type_id_01_title',
            'COUNT(FundingCaseTypeProgram_FundingProgram_funding_program_id_01_FundingProgram_FundingFormStringTranslation_funding_program_id_01.id) AS COUNT_FundingCaseTypeProgram_FundingProgram_funding_program_id_01_FundingProgram_FundingFormStringTranslation_funding_program_id_01_id',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [
            'id',
          ],
          'join' => [
            [
              'FundingProgram AS FundingCaseTypeProgram_FundingProgram_funding_program_id_01',
              'INNER',
              [
                'funding_program_id',
                '=',
                'FundingCaseTypeProgram_FundingProgram_funding_program_id_01.id',
              ],
            ],
            [
              'FundingCaseType AS FundingCaseTypeProgram_FundingCaseType_funding_case_type_id_01',
              'INNER',
              [
                'funding_case_type_id',
                '=',
                'FundingCaseTypeProgram_FundingCaseType_funding_case_type_id_01.id',
              ],
            ],
            [
              'FundingFormStringTranslation AS FundingCaseTypeProgram_FundingProgram_funding_program_id_01_FundingProgram_FundingFormStringTranslation_funding_program_id_01',
              'LEFT',
              [
                'FundingCaseTypeProgram_FundingProgram_funding_program_id_01.id',
                '=',
                'FundingCaseTypeProgram_FundingProgram_funding_program_id_01_FundingProgram_FundingFormStringTranslation_funding_program_id_01.funding_program_id',
              ],
              [
                'FundingCaseTypeProgram_FundingProgram_funding_program_id_01_FundingProgram_FundingFormStringTranslation_funding_program_id_01.funding_case_type_id:name',
                '=',
                'funding_case_type_id:name',
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
    'name' => 'SavedSearch_funding_form_translation_SearchDisplay_table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('table'),
        'saved_search_id.name' => 'funding_form_translation',
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
              'key' => 'GROUP_FIRST_FundingCaseTypeProgram_FundingProgram_funding_program_id_01_title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_FIRST_FundingCaseTypeProgram_FundingCaseType_funding_case_type_id_01_title',
              'dataType' => 'String',
              'label' => E::ts('Funding Case Type'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'COUNT_FundingCaseTypeProgram_FundingProgram_funding_program_id_01_FundingProgram_FundingFormStringTranslation_funding_program_id_01_id',
              'dataType' => 'Integer',
              'label' => E::ts('Number of Strings'),
              'sortable' => FALSE,
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'task' => 'extractStrings',
                  'entity' => 'FundingCaseTypeProgram',
                  'join' => '',
                  'target' => 'crm-popup',
                  'text' => E::ts('Extract strings'),
                  'style' => 'default',
                  'path' => '',
                  'action' => '',
                  'icon' => 'fa-file-export',
                  'conditions' => [],
                ],
                [
                  'path' => 'civicrm/funding/form-string-translation/list#?funding_program_id=[FundingCaseTypeProgram_FundingProgram_funding_program_id_01.id]&funding_case_type_id=[FundingCaseTypeProgram_FundingCaseType_funding_case_type_id_01.id]',
                  'icon' => 'fa-pen-to-square',
                  'text' => E::ts('Edit strings'),
                  'style' => 'default',
                  'conditions' => [],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => 'crm-popup',
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
