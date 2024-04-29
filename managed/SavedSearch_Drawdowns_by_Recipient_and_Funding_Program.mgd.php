<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

// phpcs:disable Generic.Files.LineLength.TooLong
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
        'api_entity' => 'FundingDrawdown',
        'api_params' => [
          'version' => 4,
          'select' => [
            'COUNT(id) AS COUNT_id',
            'GROUP_CONCAT(DISTINCT FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.title) AS GROUP_CONCAT_FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01_title',
            'GROUP_CONCAT(DISTINCT FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01.recipient_contact_id.display_name) AS GROUP_CONCAT_FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_recipient_contact_id_display_name',
            'SUM(FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01.amount_approved) AS SUM_FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_amount_approved',
            'SUM(amount) AS SUM_amount',
            'SUM(amount_paid_out) AS SUM_amount_paid_out',
            'SUM(amount_new) AS SUM_amount_new',
            'SUM(creation_date) AS SUM_creation_date',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [
            'FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01.recipient_contact_id',
            'FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01.funding_program_id',
          ],
          'join' => [
            [
              'FundingPayoutProcess AS FundingDrawdown_FundingPayoutProcess_payout_process_id_01',
              'LEFT',
              [
                'payout_process_id',
                '=',
                'FundingDrawdown_FundingPayoutProcess_payout_process_id_01.id',
              ],
            ],
            [
              'FundingCase AS FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01',
              'LEFT',
              [
                'FundingDrawdown_FundingPayoutProcess_payout_process_id_01.funding_case_id',
                '=',
                'FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01.id',
              ],
            ],
            [
              'FundingProgram AS FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01',
              'LEFT',
              [
                'FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01.funding_program_id',
                '=',
                'FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01.id',
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
    'name' => 'SavedSearch_Drawdowns_by_Recipient_and_Funding_Program_SearchDisplay_Drawdowns_by_Recipient_and_Funding_Program_Table',
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
              'key' => 'GROUP_CONCAT_FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_FundingCase_FundingProgram_funding_program_id_01_title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_CONCAT_FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_recipient_contact_id_display_name',
              'dataType' => 'String',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'SUM_FundingDrawdown_FundingPayoutProcess_payout_process_id_01_FundingPayoutProcess_FundingCase_funding_case_id_01_amount_approved',
              'dataType' => 'Money',
              'label' => E::ts('Amount Approved'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'SUM_amount',
              'dataType' => 'Money',
              'label' => E::ts('Drawdowns'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'SUM_amount_paid_out',
              'dataType' => 'Money',
              'label' => E::ts('Amount Paid Out'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'SUM_amount_new',
              'dataType' => 'Money',
              'label' => E::ts('Open Drawdowns'),
              'sortable' => TRUE,
            ],
          ],
          'actions' => [
            'download',
          ],
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
