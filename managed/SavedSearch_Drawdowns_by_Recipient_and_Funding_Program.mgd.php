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
            'GROUP_FIRST(FundingCase_FundingProgram_funding_program_id_01.title) AS GROUP_FIRST_FundingCase_FundingProgram_funding_program_id_01_title',
            'GROUP_FIRST(recipient_contact_id.display_name) AS GROUP_FIRST_recipient_contact_id_display_name',
            'SUM(amount_approved) AS SUM_amount_approved',
            'SUM(amount_drawdowns) AS SUM_amount_drawdowns',
            'SUM(amount_paid_out) AS SUM_amount_paid_out',
            'SUM(amount_drawdowns_open) AS SUM_amount_drawdowns_open',
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
              'key' => 'GROUP_FIRST_FundingCase_FundingProgram_funding_program_id_01_title',
              'dataType' => 'String',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'GROUP_FIRST_recipient_contact_id_display_name',
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
              'key' => 'SUM_amount_drawdowns',
              'dataType' => 'Money',
              'label' => E::ts('Drawdowns'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'SUM_amount_paid_out',
              'dataType' => 'Money',
              'label' => E::ts('Amount Paid Out'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'COUNT_id',
              'dataType' => 'Integer',
              'label' => E::ts('Withdrawable Funds'),
              'sortable' => TRUE,
              'rewrite' => '{($SUM_amount_approved - $SUM_amount_paid_out)|crmMoney:$currency}',
            ],
            [
              'type' => 'field',
              'key' => 'SUM_amount_drawdowns_open',
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
