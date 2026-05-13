<?php

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_snapshot_creation',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_snapshot_creation',
        'table_name' => 'civicrm_value_funding_application_snapshot_creation',
        'title' => E::ts('Funding Application Snapshot Creation'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_application_snapshot_creation',
        ],
        'style' => 'Inline',
        'collapse_display' => FALSE,
        'help_pre' => '',
        'help_post' => '',
        'weight' => 1,
        'is_active' => TRUE,
        'is_multiple' => FALSE,
        'collapse_adv_display' => TRUE,
        'is_reserved' => TRUE,
        'is_public' => TRUE,
        'icon' => '',
      ],
    ],
  ],
  [
    'name' => 'CustomField_funding_application_snapshot_creation.snapshot_id',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_snapshot_creation',
        'name' => 'snapshot_id',
        'label' => E::ts('Snapshot ID'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'snapshot_id',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
];
