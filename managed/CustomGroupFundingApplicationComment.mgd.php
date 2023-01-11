<?php

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_comment',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_comment',
        'table_name' => 'civicrm_value_funding_application_comment',
        'title' => E::ts('Funding Application Comment'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_application_comment',
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
    'name' => 'CustomField_funding_application_comment.action',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_comment',
        'name' => 'action',
        'label' => E::ts('Action'),
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => TRUE,
        'text_length' => 64,
        'column_name' => 'action',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
];
