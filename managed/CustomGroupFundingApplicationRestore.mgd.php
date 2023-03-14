<?php

declare(strict_types = 1);

use Civi\Funding\ApplicationProcess\TaskType;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_restore',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'match' => ['name'],
      'version' => 4,
      'values' => [
        'name' => 'funding_application_restore',
        'table_name' => 'civicrm_value_funding_application_restore',
        'title' => E::ts('Funding Application Restore'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_application_restore',
        ],
        'style' => 'Inline',
        'collapse_display' => TRUE,
        'weight' => 1,
        'is_active' => TRUE,
        'is_multiple' => FALSE,
        'collapse_adv_display' => TRUE,
        'is_reserved' => TRUE,
        'is_public' => FALSE,
      ],
    ],
  ],
  [
    'name' => 'CustomField_funding_application_restore.application_snapshot_id',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'match' => ['name'],
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_restore',
        'name' => 'application_snapshot_id',
        'label' => E::ts('Application snapshot ID'),
        'data_type' => 'Int',
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => FALSE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => FALSE,
        'text_length' => 255,
        'column_name' => 'application_snapshot_id',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
];
