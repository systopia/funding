<?php

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_review_status_change',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_review_status_change',
        'table_name' => 'civicrm_value_funding_application_review_status_change',
        'title' => E::ts('Funding Application Review Status Change'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_application_review_status_change',
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
    'name' => 'CustomField_funding_application_review_status_change.from_calculative_status',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_review_status_change',
        'name' => 'from_is_review_calculative',
        'label' => E::ts('From Review Calculative'),
        'data_type' => 'Boolean',
        // When using CheckBox the field serialize is set to 1...
        'html_type' => 'Select',
        'is_required' => FALSE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'from_is_review_calculative',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
  [
    'name' => 'CustomField_funding_application_review_status_change.to_calculative_status',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_review_status_change',
        'name' => 'to_is_review_calculative',
        'label' => E::ts('To Review Calculative'),
        'data_type' => 'Boolean',
        // When using CheckBox the field serialize is set to 1...
        'html_type' => 'Select',
        'is_required' => FALSE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'to_is_review_calculative',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
  [
    'name' => 'CustomField_funding_application_review_status_change.from_content_status',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_review_status_change',
        'name' => 'from_is_review_content',
        'label' => E::ts('From Review Content'),
        'data_type' => 'Boolean',
        // When using CheckBox the field serialize is set to 1..
        'html_type' => 'Select',
        'is_required' => FALSE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'from_is_review_content',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
  [
    'name' => 'CustomField_funding_application_review_status_change.to_content_status',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_review_status_change',
        'name' => 'to_is_review_content',
        'label' => E::ts('To Review Content'),
        'data_type' => 'Boolean',
        // When using CheckBox the field serialize is set to 1...
        'html_type' => 'Select',
        'is_required' => FALSE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => TRUE,
        'column_name' => 'to_is_review_content',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
];
