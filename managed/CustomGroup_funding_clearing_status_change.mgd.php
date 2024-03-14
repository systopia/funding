<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_clearing_status_change',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_clearing_status_change',
        'title' => E::ts('Funding Clearing Status Change'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_clearing_status_change',
        ],
        'style' => 'Inline',
        'help_pre' => '',
        'help_post' => '',
        'collapse_adv_display' => TRUE,
        'is_reserved' => TRUE,
        'icon' => '',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_clearing_status_change_CustomField_from_status',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_status_change',
        'name' => 'from_status',
        'label' => E::ts('From Status'),
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'text_length' => 64,
        'column_name' => 'from_status',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_clearing_status_change_CustomField_to_status',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_status_change',
        'name' => 'to_status',
        'label' => E::ts('To Status'),
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'text_length' => 64,
        'column_name' => 'to_status',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
