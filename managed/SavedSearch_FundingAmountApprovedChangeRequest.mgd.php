<?php

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_amount_approved_change_requests',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_amount_approved_change_requests',
        'label' => E::ts('Funding Amount Approved Change Requests'),
        'api_entity' => 'FundingAmountApprovedChangeRequest',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'status:label',
            'amount_approved',
            'amount_requested',
            'comment',
            'creation_date',
            'creation_contact_id.display_name',
            'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.id',
            'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.identifier',
            'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
            'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.amount_approved',
            'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.funding_program_id:label',
            'CAN_review',
          ],
          'orderBy' => [
            'id' => 'DESC',
          ],
          'where' => [],
          'groupBy' => [],
          'join' => [
            [
              'FundingCase AS FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01',
              'LEFT',
              [
                'funding_case_id',
                '=',
                'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.id',
              ],
            ],
            [
              'FundingApplicationProcess AS FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01_FundingCase_FundingApplicationProcess_funding_case_id_01',
              'LEFT',
              [
                'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.id',
                '=',
                'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01_FundingCase_FundingApplicationProcess_funding_case_id_01.funding_case_id',
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
    'name' => 'SearchDisplay_funding_amount_approved_change_requests.table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Funding Amount Approved Change Requests Table'),
        'saved_search_id.name' => 'funding_amount_approved_change_requests',
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
              'key' => 'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.funding_program_id:label',
              'label' => E::ts('Funding Program'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.identifier',
              'label' => E::ts('Funding Case Identifier'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.recipient_contact_id.display_name',
              'label' => E::ts('Recipient'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'FundingAmountApprovedChangeRequest_FundingCase_funding_case_id_01.amount_approved',
              'label' => E::ts('Amount Approved'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
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
