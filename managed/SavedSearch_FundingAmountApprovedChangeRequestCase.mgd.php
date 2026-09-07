<?php

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_amount_approved_change_requests_case',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_amount_approved_change_requests_case',
        'label' => E::ts('Funding Amount Approved Change Requests (Case)'),
        'api_entity' => 'FundingAmountApprovedChangeRequest',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'funding_case_id',
            'status:label',
            'amount_approved',
            'amount_requested',
            'comment',
            'creation_date',
            'creation_contact_id.display_name',
            'decision_date',
            'decision_contact_id.display_name',
            'CAN_review',
          ],
          'orderBy' => [
            'id' => 'DESC',
          ],
          'where' => [],
          'groupBy' => [],
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
    'name' => 'SearchDisplay_funding_amount_approved_change_requests_case.table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Funding Amount Approved Change Requests Table (Case)'),
        'saved_search_id.name' => 'funding_amount_approved_change_requests_case',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 10,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'status:label',
              'label' => E::ts('Status'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount_requested',
              'label' => E::ts('Amount Requested'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'amount_approved',
              'label' => E::ts('Amount Accepted'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'creation_date',
              'label' => E::ts('Creation Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'creation_contact_id.display_name',
              'label' => E::ts('Creation Contact'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'comment',
              'label' => E::ts('Comment'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'decision_date',
              'label' => E::ts('Decision Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'decision_contact_id.display_name',
              'label' => E::ts('Decision Contact'),
              'sortable' => TRUE,
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'entity' => 'FundingAmountApprovedChangeRequest',
                  'task' => 'approve',
                  'icon' => 'fa-thumbs-up',
                  'text' => E::ts('Approve'),
                  'style' => 'success',
                  'path' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'conditions' => [],
                ],
                [
                  'entity' => 'FundingAmountApprovedChangeRequest',
                  'task' => 'approvePartial',
                  'icon' => 'fa-thumbs-up',
                  'text' => E::ts('Approve Partial'),
                  'style' => 'success',
                  'path' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'conditions' => [],
                ],
                [
                  'entity' => 'FundingAmountApprovedChangeRequest',
                  'task' => 'reject',
                  'icon' => 'fa-save',
                  'text' => E::ts('Reject'),
                  'style' => 'danger',
                  'path' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'conditions' => [],
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
              'label' => E::ts('Actions'),
            ]
          ],
          'actions' => [
            'approve',
            'approvePartial',
            'reject',
          ],
          'classes' => [
            'table',
            'table-striped',
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
