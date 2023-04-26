<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_funding_drawdowns',
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_drawdowns',
        'label' => E::ts('Drawdowns'),
        'form_values' => NULL,
        'mapping_id' => NULL,
        'search_custom_id' => NULL,
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
        'expires_date' => NULL,
        'description' => NULL,
      ],
    ],
  ],
  [
    'name' => 'SearchDisplay_funding_drawdowns.table',
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
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
          'limit' => 0,
          'pager' => FALSE,
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'creation_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Creation Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'amount',
              'dataType' => 'Money',
              'label' => E::ts('Amount'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'requester_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Requester'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'status:label',
              'dataType' => 'String',
              'label' => E::ts('Status'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'acception_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Acception Date'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'reviewer_contact_id.display_name',
              'dataType' => 'String',
              'label' => E::ts('Reviewer'),
              'sortable' => TRUE,
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
                ],
                [
                  'path' => 'civicrm/funding/payment-instruction/download?drawdownId=[id]',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('Download Payment Instruction'),
                  'style' => 'default',
                  'condition' => [
                    'acception_date',
                    'IS NOT EMPTY',
                  ],
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => '_blank',
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
              'label' => E::ts('Actions'),
            ],
          ],
          'actions' => FALSE,
          'classes' => [
            'table',
            'table-striped',
          ],
        ],
        'acl_bypass' => FALSE,
      ],
    ],
  ],
];
