<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_program_controlling',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_program_controlling',
        'label' => E::ts('Funding Program Controlling'),
        'api_entity' => 'FundingProgram',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'title',
            'budget',
            'amount_eligible',
            'amount_approved',
            'amount_available',
            'amount_paid_out',
            'amount_cleared',
            'amount_admitted',
            'end_date',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [
            'id',
          ],
          'join' => [],
          'having' => [],
        ],
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_funding_program_controlling_SearchDisplay_table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_program_controlling',
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
              'key' => 'title',
              'dataType' => 'String',
              'label' => E::ts('Title'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'budget',
              'dataType' => 'Money',
              'label' => E::ts('Budget'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_eligible',
              'dataType' => 'Money',
              'label' => E::ts('Amount Eligible'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_approved',
              'dataType' => 'Money',
              'label' => E::ts('Amount Approved'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_available',
              'dataType' => 'Money',
              'label' => E::ts('Amount Available'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_paid_out',
              'dataType' => 'Money',
              'label' => E::ts('Amount Paid Out'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_admitted',
              'dataType' => 'Money',
              'label' => E::ts('Amount Admitted'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_cleared',
              'dataType' => 'Money',
              'label' => E::ts('Amount Cleared'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'end_date',
              'dataType' => 'Date',
              'label' => E::ts('End Date'),
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
