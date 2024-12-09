<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_Drawdowns_by_Recipient_and_Funding_Program',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Drawdowns_by_Recipient_and_Funding_Program',
        'label' => E::ts('Drawdowns by Recipient and Funding Program'),
        'api_entity' => 'FundingCase',
        'api_params' => [
          'version' => 4,
          'select' => [
            'COUNT(id) AS COUNT_id',
            'GROUP_CONCAT(DISTINCT FundingCase_FundingProgram_funding_program_id_01.title) AS GROUP_CONCAT_FundingCase_FundingProgram_funding_program_id_01_title',
            'GROUP_CONCAT(DISTINCT recipient_contact_id.display_name) AS GROUP_CONCAT_recipient_contact_id_display_name',
            'SUM(amount_approved) AS SUM_amount_approved',
            'SUM(FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01.amount) AS SUM_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_amount',
            'SUM(FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01.amount_paid_out) AS SUM_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_amount_paid_out',
            'SUM(FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01.amount_new) AS SUM_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_amount_new',
            'GROUP_FIRST(FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01.creation_date) AS GROUP_FIRST_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_creation_date',
            'GROUP_FIRST(FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01.acception_date) AS GROUP_FIRST_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_acception_date',
          ],
          'orderBy' => [],
          'where' => [
            [
              'amount_approved',
              '>=',
              '0',
            ],
          ],
          'groupBy' => [
            'funding_program_id',
            'recipient_contact_id',
          ],
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
              'FundingPayoutProcess AS FundingCase_FundingPayoutProcess_funding_case_id_01',
              'LEFT',
              [
                'id',
                '=',
                'FundingCase_FundingPayoutProcess_funding_case_id_01.funding_case_id',
              ],
            ],
            [
              'FundingDrawdown AS FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01',
              'LEFT',
              [
                'FundingCase_FundingPayoutProcess_funding_case_id_01.id',
                '=',
                'FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01.payout_process_id',
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
    'name' => 'SavedSearch_Drawdowns_by_Recipient_and_Funding_Program_SearchDisplay_Table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'Table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'Drawdowns_by_Recipient_and_Funding_Program',
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
              'key' => 'GROUP_CONCAT_FundingCase_FundingProgram_funding_program_id_01_title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_CONCAT_recipient_contact_id_display_name',
              'dataType' => 'String',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'SUM_amount_approved',
              'dataType' => 'Money',
              'label' => E::ts('Amount Approved'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'SUM_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_amount',
              'dataType' => 'Money',
              'label' => E::ts('Drawdowns'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'SUM_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_amount_paid_out',
              'dataType' => 'Money',
              'label' => E::ts('Amount Paid Out'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'SUM_FundingCase_FundingPayoutProcess_funding_case_id_01_FundingPayoutProcess_FundingDrawdown_payout_process_id_01_amount_new',
              'dataType' => 'Money',
              'label' => E::ts('Open Drawdowns'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
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
