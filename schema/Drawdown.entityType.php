<?php
/**
 * Copyright (C) 2026 SYSTOPIA GmbH
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
  'name' => 'FundingDrawdown',
  'table' => 'civicrm_funding_drawdown',
  'class' => 'CRM_Funding_DAO_Drawdown',
  'getInfo' => fn() => [
    'title' => E::ts('Drawdown'),
    'title_plural' => E::ts('Drawdowns'),
    'description' => E::ts('Drawdowns in a payout process'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'index_status' => [
      'fields' => [
        'status' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique FundingDrawdown ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'payout_process_id' => [
      'title' => E::ts('Payout Process ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to FundingPayoutProcess'),
      'pseudoconstant' => [
        'table' => 'civicrm_funding_payout_process',
        'key_column' => 'id',
        'label_column' => 'id',
        'prefetch' => 'false',
      ],
      'entity_reference' => [
        'entity' => 'PayoutProcess',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'status' => [
      'title' => E::ts('Status'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'required' => TRUE,
      'pseudoconstant' => [
        'callback' => 'Civi\\Funding\\FundingPseudoConstants::getDrawdownStatus',
      ],
    ],
    'creation_date' => [
      'title' => E::ts('Creation Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'amount' => [
      'title' => E::ts('Amount'),
      'sql_type' => 'decimal(10,2)',
      'input_type' => 'Text',
      'data_type' => 'Money',
    ],
    'acception_date' => [
      'title' => E::ts('Acception Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'requester_contact_id' => [
      'title' => E::ts('Requester Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'reviewer_contact_id' => [
      'title' => E::ts('Reviewer Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
  ],
];
