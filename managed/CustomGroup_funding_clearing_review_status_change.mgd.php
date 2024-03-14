<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_clearing_review_status_change',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_clearing_review_status_change',
        'title' => E::ts('Funding Clearing Review Status Change'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_clearing_review_status_change',
        ],
        'style' => 'Inline',
        'help_pre' => '',
        'help_post' => '',
        'weight' => 2,
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
    'name' => 'CustomGroup_funding_clearing_review_status_change_CustomField_from_is_review_calculative',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_review_status_change',
        'name' => 'from_is_review_calculative',
        'label' => E::ts('From Review Calculative'),
        'data_type' => 'Boolean',
        'html_type' => 'Select',
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'from_is_review_calculative',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_clearing_review_status_change_CustomField_to_is_review_calculative',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_review_status_change',
        'name' => 'to_is_review_calculative',
        'label' => E::ts('To Review Calculative'),
        'data_type' => 'Boolean',
        'html_type' => 'Select',
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'to_is_review_calculative',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_clearing_review_status_change_CustomField_from_is_review_content',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_review_status_change',
        'name' => 'from_is_review_content',
        'label' => E::ts('From Review Content'),
        'data_type' => 'Boolean',
        'html_type' => 'Select',
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'from_is_review_content',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_clearing_review_status_change_CustomField_to_is_review_content',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_review_status_change',
        'name' => 'to_is_review_content',
        'label' => E::ts('To Review Content'),
        'data_type' => 'Boolean',
        'html_type' => 'Select',
        'is_searchable' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'to_is_review_content',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
