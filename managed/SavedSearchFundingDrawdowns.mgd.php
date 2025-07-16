<?php
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_drawdowns',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_drawdowns',
        'label' => E::ts('Drawdowns (in Funding Case)'),
        'api_entity' => 'FundingDrawdown',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'payout_process_id',
            'creation_date',
            'amount',
            'requester_contact_id.display_name',
            'status:label',
            'acception_date',
            'reviewer_contact_id.display_name',
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
    'name' => 'SearchDisplay_funding_drawdowns.table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'table',
        'label' => E::ts('Table'),
        'saved_search_id.name' => 'funding_drawdowns',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [],
          'limit' => 10,
          'pager' => [
            'show_count' => FALSE,
            'expose_limit' => TRUE,
          ],
          'placeholder' => 5,
          'columns' => [
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
              'key' => 'amount',
              'dataType' => 'Money',
              'label' => E::ts('Amount'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => 'SUM',
              ],
            ],
            [
              'type' => 'field',
              'key' => 'requester_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Requester'),
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
              'key' => 'acception_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Acception Date'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'type' => 'field',
              'key' => 'reviewer_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Reviewer'),
              'sortable' => TRUE,
              'tally' => [
                'fn' => NULL,
              ],
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'path' => 'civicrm/funding/drawdown/accept?drawdownId=[id]',
                  'icon' => 'fa-thumbs-up',
                  'text' => E::ts('Accept'),
                  'style' => 'success',
                  'condition' => [
                    'CAN_review',
                    '=',
                    TRUE,
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/funding/drawdown/reject?drawdownId=[id]',
                  'icon' => 'fa-thumbs-down',
                  'text' => E::ts('Reject'),
                  'style' => 'danger',
                  'condition' => [
                    'CAN_review',
                    '=',
                    TRUE,
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '',
                  'task' => '',
                ],
                [
                  'path' => 'civicrm/funding/drawdown-document/download?drawdownId=[id]',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Download Document'),
                  'style' => 'default',
                  'condition' => [
                    'acception_date',
                    'IS NOT EMPTY',
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                  'task' => '',
                ],
              ],
              'type' => 'buttons',
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
          'actions_display_mode' => 'menu',
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
